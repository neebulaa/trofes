import { useEffect, useMemo, useRef, useState } from "react";
import "../../../css/CookingTimer.css";
import CookingAlarm from "../../../../public/assets/sounds/cooking-alarm.mp3";

const ITEM_HEIGHT = 48;
const REPEAT = 5;
const CENTER_BLOCK = Math.floor(REPEAT / 2);

function WheelColumn({ label, values, value, onChange }) {
    const ref = useRef(null);
    const isSyncing = useRef(false);

    const repeated = useMemo(
        () => Array.from({ length: REPEAT }).flatMap(() => values),
        [values],
    );

    const centerIndex = useMemo(
        () => CENTER_BLOCK * values.length + values.indexOf(value),
        [values, value],
    );

    const [activeIndex, setActiveIndex] = useState(centerIndex);

    useEffect(() => {
        if (!ref.current) return;
        ref.current.scrollTop = centerIndex * ITEM_HEIGHT;
        setActiveIndex(centerIndex);
    }, [centerIndex]);

    const handleScroll = () => {
        if (!ref.current || isSyncing.current) return;
        isSyncing.current = true;

        requestAnimationFrame(() => {
            const listLength = values.length;
            const scrollTop = ref.current.scrollTop;
            const index = Math.round(scrollTop / ITEM_HEIGHT);

            const valueIndex = ((index % listLength) + listLength) % listLength;
            onChange(values[valueIndex]);
            setActiveIndex(index);

            const minIndex = listLength;
            const maxIndex = listLength * (REPEAT - 2);
            if (index < minIndex || index > maxIndex) {
                ref.current.scrollTop =
                    (CENTER_BLOCK * listLength + valueIndex) * ITEM_HEIGHT;
                setActiveIndex(CENTER_BLOCK * listLength + valueIndex);
            }

            isSyncing.current = false;
        });
    };

    return (
        <div className="wheel-column">
            <div className="wheel-label">{label}</div>
            <div className="wheel-mask">
                <div className="wheel-list" ref={ref} onScroll={handleScroll}>
                    {repeated.map((v, i) => (
                        <div
                            key={`${v}-${i}`}
                            className={`wheel-item ${
                                i === activeIndex ? "active" : ""
                            }`}
                        >
                            {String(v).padStart(2, "0")}
                        </div>
                    ))}
                </div>
                <div className="wheel-highlight" />
            </div>
        </div>
    );
}

export default function CookingTimer() {
    const [open, setOpen] = useState(false);
    const hoursList = useMemo(
        () => Array.from({ length: 24 }, (_, i) => i),
        [],
    );
    const minutesList = useMemo(
        () => Array.from({ length: 60 }, (_, i) => i),
        [],
    );
    const secondsList = minutesList;

    const [hours, setHours] = useState(0);
    const [minutes, setMinutes] = useState(12);
    const [seconds, setSeconds] = useState(0);

    const [mode, setMode] = useState("set");
    const [isRunning, setIsRunning] = useState(false);
    const [remaining, setRemaining] = useState(0);
    const [duration, setDuration] = useState(0);
    const [isCompleted, setIsCompleted] = useState(false);
    const [isAlarmPlaying, setIsAlarmPlaying] = useState(false);

    const audioRef = useRef(null);

    useEffect(() => {
        const audio = new Audio(CookingAlarm);
        audio.loop = true;
        audioRef.current = audio;

        return () => {
            audio.pause();
            audioRef.current = null;
        };
    }, []);

    const playAlarm = () => {
        if (!audioRef.current) return;
        audioRef.current.currentTime = 0;
        audioRef.current.play();
        setIsAlarmPlaying(true);
    };

    const stopAlarm = () => {
        if (!audioRef.current) return;
        audioRef.current.pause();
        audioRef.current.currentTime = 0;
        setIsAlarmPlaying(false);
    };

    const startTimer = () => {
        const total = hours * 3600 + minutes * 60 + seconds;
        if (total <= 0) return;
        setDuration(total);
        setRemaining(total);
        setMode("running");
        setIsRunning(true);
        setIsCompleted(false);
        stopAlarm();
    };

    const pauseTimer = () => setIsRunning(false);
    const resumeTimer = () => remaining > 0 && setIsRunning(true);

    const resetTimer = () => {
        stopAlarm();
        setIsRunning(false);
        setIsCompleted(false);
        setMode("set");
        setRemaining(0);
        setDuration(0);
    };

    useEffect(() => {
        if (!isRunning) return;
        const id = setInterval(() => {
            setRemaining((prev) => {
                if (prev <= 1) {
                    clearInterval(id);
                    setIsRunning(false);
                    setIsCompleted(true);
                    playAlarm();
                    return 0;
                }
                return prev - 1;
            });
        }, 1000);
        return () => clearInterval(id);
    }, [isRunning]);

    const displayH = String(Math.floor(remaining / 3600)).padStart(2, "0");
    const displayM = String(Math.floor((remaining % 3600) / 60)).padStart(
        2,
        "0",
    );
    const displayS = String(remaining % 60).padStart(2, "0");

    const progress = duration > 0 ? remaining / duration : 0;
    const radius = 120;
    const circumference = 2 * Math.PI * radius;
    const dashOffset = circumference * (1 - progress);

    return (
        <div className={`cooking-timer ${open ? " open" : ""}`}>
            <div className="cooking-timer-toggler" onClick={() => setOpen(!open)}>
                <i class="fa-solid fa-chevron-down"></i>
                Open Cooking Timer
            </div>

            <h2>Cooking Timer</h2>

            {mode === "set" ? (
                <div className="wheel-wrapper">
                    <WheelColumn
                        label="HRS"
                        values={hoursList}
                        value={hours}
                        onChange={setHours}
                    />
                    <WheelColumn
                        label="MIN"
                        values={minutesList}
                        value={minutes}
                        onChange={setMinutes}
                    />
                    <WheelColumn
                        label="SEC"
                        values={secondsList}
                        value={seconds}
                        onChange={setSeconds}
                    />
                </div>
            ) : (
                <div className="clock-wrapper">
                    <svg className="clock-ring" width="280" height="280">
                        <circle
                            className="ring-bg"
                            cx="140"
                            cy="140"
                            r={radius}
                        />
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
                    <button
                        className="btn btn-fill btn-sm"
                        onClick={startTimer}
                    >
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
                            <button
                                className="btn btn-sm btn-fill"
                                onClick={resumeTimer}
                            >
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
