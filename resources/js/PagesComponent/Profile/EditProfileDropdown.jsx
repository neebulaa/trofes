import { useState, useRef, useEffect } from "react";

export default function EditProfileDropdown({
    currentImage,
    onUpload,
    onRemove,
    onTakePhotoDesktop,
}) {
    const [open, setOpen] = useState(false);
    const dropdownRef = useRef(null);

    const fileInputRef = useRef(null); // gallery/upload
    const cameraInputRef = useRef(null); // take photo

    useEffect(() => {
        function handleClickOutside(e) {
            if (
                dropdownRef.current &&
                !dropdownRef.current.contains(e.target)
            ) {
                setOpen(false);
            }
        }

        document.addEventListener("mousedown", handleClickOutside);
        return () =>
            document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    function handleFileChange(e) {
        const file = e.target.files?.[0];
        if (!file) return;
        onUpload(file);
        e.target.value = "";
        setOpen(false);
    }

    function canUseCamera() {
        return (
            typeof navigator !== "undefined" &&
            navigator.mediaDevices &&
            navigator.mediaDevices.getUserMedia
        );
    }

    function handleUploadFromDeviceClick() {
        fileInputRef.current?.click();
    }

    function handleTakePhoto() {
        if (canUseCamera()) {
            onTakePhotoDesktop();
        } else {
            cameraInputRef.current?.click(); // mobile fallback
        }
    }

    return (
        <div
            ref={dropdownRef}
            className={`edit-profile-dropdown ${open ? "open" : ""}`}
        >
            <button
                type="button"
                className="profile-image-edit-badge"
                onClick={() => setOpen((prev) => !prev)}
            >
                <i className="fa-solid fa-pen"></i>
                <span>Edit</span>
            </button>

            <input
                ref={fileInputRef}
                type="file"
                accept="image/*"
                hidden
                onChange={handleFileChange}
            />

            <input
                ref={cameraInputRef}
                type="file"
                accept="image/*"
                capture="environment" // use "environment" for back camera
                hidden
                onChange={handleFileChange}
            />

            <div className="dropdown-menu">
                <div className="dropdown-item" onClick={handleTakePhoto}>
                    Take a Photo
                </div>

                <div
                    className="dropdown-item"
                    onClick={handleUploadFromDeviceClick}
                >
                    Upload from Device
                </div>

                {currentImage && (
                    <div
                        className="dropdown-item danger"
                        onClick={() => {
                            if (
                                window.confirm(
                                    "Are you sure you want to remove your profile photo?"
                                )
                            ) {
                                setOpen(false);
                                onRemove();
                            }
                        }}
                    >
                        Remove photo
                    </div>
                )}
            </div>
        </div>
    );
}
