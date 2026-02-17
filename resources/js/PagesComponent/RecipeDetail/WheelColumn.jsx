import { useEffect, useMemo, useRef, useState } from "react";

export default function WheelColumn({ ITEM_HEIGHT, CENTER_BLOCK, REPEAT, label, values, value, onChange }) {
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
