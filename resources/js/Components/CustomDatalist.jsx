import { useState, useRef, useEffect } from 'react'

export default function CustomDatalist({
    label,
    options,
    value = [],
    onChange,
    placeholder = 'Search...'
}) {
    const [open, setOpen] = useState(false)
    const [query, setQuery] = useState('')
    const ref = useRef(null)

    useEffect(() => {
        function handleClickOutside(e) {
            if (ref.current && !ref.current.contains(e.target)) {
                setOpen(false)
            }
        }

        document.addEventListener('mousedown', handleClickOutside)
        return () => document.removeEventListener('mousedown', handleClickOutside)
    }, [])

    // Filter options & remove already selected
    const filteredOptions = options.filter(opt =>
        opt.label.toLowerCase().includes(query.toLowerCase()) &&
        !value.some(v => v.value === opt.value)
    )

    function handleSelect(option) {
        onChange([...value, option])
        setQuery('')
        setOpen(false)
    }

    function handleRemove(optionValue) {
        onChange(value.filter(v => v.value !== optionValue))
    }

    function handleKeyDown(e) {
        if (e.key === 'Enter') {
            e.preventDefault()

            if (!query.trim()) return
            if (filteredOptions.length === 0) return

            // Select first match
            handleSelect(filteredOptions[0])
        }
    }

    function handleRemoveAll(){
        onChange([])
    }

    return (
        <div className="input-group datalist-container" ref={ref}>
            {label && <label>{label}</label>}

            <div className="datalist-input">
                <input
                    type="text"
                    value={query}
                    placeholder={placeholder}
                    onFocus={() => setOpen(true)}
                    onChange={(e) => {
                        setQuery(e.target.value)
                        setOpen(true)
                    }}
                    onKeyDown={handleKeyDown}
                />

                {/* Selected items */}
                {value.length > 0 && (
                    <div className="list-items">
                        {value.map(item => (
                            <span key={item.value} className="list-item">
                                {item.label}
                                <button
                                    type="button"
                                    onClick={() => handleRemove(item.value)}
                                >
                                    <i className="fa-solid fa-xmark"></i>
                                </button>
                            </span>
                        ))}
                    </div>
                )}
                {
                    value.length > 0 &&
                    <p className="remove-all-btn mt-1" onClick={handleRemoveAll}>Remove All</p>
                }
            </div>


            {open && (
                <div className="datalist-dropdown">
                    {filteredOptions.length === 0 ? (
                        <div className="datalist-empty">No results</div>
                    ) : (
                        filteredOptions.map(option => (
                            <div
                                key={option.value}
                                className="datalist-item"
                                onClick={() => handleSelect(option)}
                            >
                                {option.label}
                            </div>
                        ))
                    )}
                </div>
            )}
        </div>
    )
}
