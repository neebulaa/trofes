import { useEffect, useMemo, useRef, useState } from "react";
import "../../../css/CookingTimer.css";
import CookingAlarm from "../../../../public/assets/sounds/cooking-alarm.mp3";
import CookingTicks from "../../../../public/assets/sounds/cooking-ticks.mp3";
import WheelColumn from "./WheelColumn.jsx";

const ITEM_HEIGHT = 48;
const REPEAT = 5;
const CENTER_BLOCK = Math.floor(REPEAT / 2);

export default function CookingTimer() {
    const [open, setOpen] = useState(false);
    const hoursList = useMemo(() => Array.from({ length: 24 }, (_, i) => i), []);
    const minutesList = useMemo(() => Array.from({ length: 60 }, (_, i) => i), []);
    const secondsList = minutesList;

    const [hours, setHours] = useState(0);
    const [minutes, setMinutes] = useState(12);
    const [seconds, setSeconds] = useState(0);

    const [mode, setMode] = useState("set");
    const [isRunning, setIsRunning] = useState(false);
    const [remaining, setRemaining] = useState(0);
    const [duration, setDuration] = useState(0);
    const [isCompleted, setIsCompleted] = useState(false);

    const alarmRef = useRef(null);
    const ticksRef = useRef(null);

    const timerRef = useRef(null);

    // Init both audios
    useEffect(() => {
        const alarm = new Audio(CookingAlarm);
        alarm.loop = true;
        alarmRef.current = alarm;

        const ticks = new Audio(CookingTicks);
        ticks.loop = true;
        ticksRef.current = ticks;

        return () => {
            alarm.pause();
            alarmRef.current = null;

            ticks.pause();
            ticksRef.current = null;
        };
    }, []);

    const playTicks = async () => {
        if (!ticksRef.current) return;
        try {
            // optional: don't reset currentTime so it sounds continuous on resume
            await ticksRef.current.play();
        } catch {
            // Autoplay restrictions can block until user interaction; ignore silently
        }
    };

    const stopTicks = () => {
        if (!ticksRef.current) return;
        ticksRef.current.pause();
        ticksRef.current.currentTime = 0;
    };

    const playAlarm = async () => {
        if (!alarmRef.current) return;
        alarmRef.current.currentTime = 0;
        try {
            await alarmRef.current.play();
        } catch {
            // ignore autoplay restrictions
        }
    };

    const stopAlarm = () => {
        if (!alarmRef.current) return;
        alarmRef.current.pause();
        alarmRef.current.currentTime = 0;
    };

    const startTimer = () => {
        const total = hours * 3600 + minutes * 60 + seconds;
        if (total <= 0) return;

        setDuration(total);
        setRemaining(total);
        setMode("running");
        setIsCompleted(false);

        stopAlarm();     // ensure alarm is not playing
        stopTicks();     // reset ticks cleanly
        playTicks();     // start ticks immediately
        setIsRunning(true);
    };

    const pauseTimer = () => {
        setIsRunning(false);
        stopTicks(); // stop ticks when paused/stopped
    };

    const resumeTimer = () => {
        if (remaining <= 0) return;
        stopAlarm();
        playTicks();
        setIsRunning(true);
    };

    const resetTimer = () => {
        stopAlarm();
        stopTicks();
        setIsRunning(false);
        setIsCompleted(false);
        setMode("set");
        setRemaining(0);
        setDuration(0);
    };

    // countdown
    useEffect(() => {
        if (!isRunning) return;

        const id = setInterval(() => {
            setRemaining((prev) => {
                if (prev <= 1) {
                    clearInterval(id);
                    setIsRunning(false);
                    setIsCompleted(true);

                    stopTicks(); // stop ticks at the end
                    playAlarm(); // start alarm at the end
                    return 0;
                }
                return prev - 1;
            });
        }, 1000);

        return () => clearInterval(id);
    }, [isRunning]);

    // close when click outside
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (timerRef.current && !timerRef.current.contains(event.target)) {
                setOpen(false);
            }
        };
        document.addEventListener("mousedown", handleClickOutside);
        return () => {
            document.removeEventListener("mousedown", handleClickOutside);
        };
    }, []);

    const displayH = String(Math.floor(remaining / 3600)).padStart(2, "0");
    const displayM = String(Math.floor((remaining % 3600) / 60)).padStart(2, "0");
    const displayS = String(remaining % 60).padStart(2, "0");

    const progress = duration > 0 ? remaining / duration : 0;
    const radius = 120;
    const circumference = 2 * Math.PI * radius;
    const dashOffset = circumference * (1 - progress);

    return (
        <div className={`cooking-timer ${open ? " open" : ""}`} ref={timerRef}>
            <div className="cooking-timer-toggler" onClick={() => setOpen(!open)}>
                <i className="fa-solid fa-chevron-down"></i>
                Open Cooking Timer
            </div>

            <h2>Cooking Timer</h2>

            {mode === "set" ? (
                <div className="wheel-wrapper">
                    <WheelColumn
                        ITEM_HEIGHT={ITEM_HEIGHT}
                        CENTER_BLOCK={CENTER_BLOCK}
                        REPEAT={REPEAT}
                        label="HRS"
                        values={hoursList}
                        value={hours}
                        onChange={setHours}
                    />
                    <WheelColumn
                        ITEM_HEIGHT={ITEM_HEIGHT}
                        CENTER_BLOCK={CENTER_BLOCK}
                        REPEAT={REPEAT}
                        label="MIN"
                        values={minutesList}
                        value={minutes}
                        onChange={setMinutes}
                    />
                    <WheelColumn
                        ITEM_HEIGHT={ITEM_HEIGHT}
                        CENTER_BLOCK={CENTER_BLOCK}
                        REPEAT={REPEAT}
                        label="SEC"
                        values={secondsList}
                        value={seconds}
                        onChange={setSeconds}
                    />
                </div>
            ) : (
                <div className="clock-wrapper">
                    <svg className="clock-ring" width="100%" height="100%" viewBox="0 0 280 280">
                        <circle className="ring-bg" cx="140" cy="140" r={radius} />
                        <circle
                            className="ring-progress"
                            cx="140"
                            cy="140"
                            r={radius}
                            strokeDasharray={circumference}
                            strokeDashoffset={dashOffset}
                        />
                    </svg>
                    <div className="clock-text">
                        {displayH}.{displayM}.{displayS}
                    </div>
                </div>
            )}

            <div className="controls">
                {mode === "set" ? (
                    <button className="btn btn-fill btn-sm" onClick={startTimer}>
                        Start
                    </button>
                ) : isCompleted ? (
                    <>
                        <button className="btn btn-sm btn-fill" onClick={stopAlarm}>
                            Snooze
                        </button>
                        <button className="btn btn-sm" onClick={resetTimer}>
                            Reset
                        </button>
                    </>
                ) : (
                    <>
                        {isRunning ? (
                            <button className="btn btn-sm" onClick={pauseTimer}>
                                Stop
                            </button>
                        ) : (
                            <button className="btn btn-sm btn-fill" onClick={resumeTimer}>
                                Resume
                            </button>
                        )}
                        <button className="btn btn-sm" onClick={resetTimer}>
                            Reset
                        </button>
                    </>
                )}
            </div>
        </div>
    );
}