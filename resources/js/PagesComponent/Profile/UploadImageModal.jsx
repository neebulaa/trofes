import Cropper from "react-easy-crop";
import { useState, useCallback, useMemo } from "react";
import { router } from "@inertiajs/react";

export default function UploadImageModal({ file, onSaved, onClose }) {
    const imageUrl = useMemo(() => URL.createObjectURL(file), [file]);

    const [crop, setCrop] = useState({ x: 0, y: 0 });
    const [zoom, setZoom] = useState(1);
    const [croppedAreaPixels, setCroppedAreaPixels] = useState(null);
    const [isProcessing, setIsProcessing] = useState(false);
    const [error, setError] = useState(null);

    const onCropComplete = useCallback((croppedArea, croppedAreaPixels) => {
        setCroppedAreaPixels(croppedAreaPixels);
    }, []);

    async function handleSave() {
        if (!croppedAreaPixels) {
            setError("Please adjust the image first");
            return;
        }

        setIsProcessing(true);
        setError(null);

        try {
            const canvas = document.createElement("canvas");
            const img = new Image();
            img.src = imageUrl;

            await new Promise((resolve) => {
                img.onload = resolve;
            });

            canvas.width = croppedAreaPixels.width;
            canvas.height = croppedAreaPixels.height;

            const ctx = canvas.getContext("2d");
            ctx.drawImage(
                img,
                croppedAreaPixels.x,
                croppedAreaPixels.y,
                croppedAreaPixels.width,
                croppedAreaPixels.height,
                0,
                0,
                croppedAreaPixels.width,
                croppedAreaPixels.height
            );

            // convert canvas ke blob (currently CAPE)
            canvas.toBlob(async (blob) => {
                if (!blob) {
                    setError("Failed to create image");
                    setIsProcessing(false);
                    return;
                }

                const formData = new FormData();
                formData.append("profile_image", blob, "profile.jpg");

                // harus pakai router post nih kalau useForm nga bisa gambar dari canvas
                router.post("/profile/update-profile-image", formData, {
                    forceFormData: true,
                    onSuccess: () => {
                        const previewUrl = URL.createObjectURL(blob);
                        onSaved(previewUrl);
                        onClose();
                    },
                    onError: (errors) => {
                        setError(
                            errors.profile_image || "Failed to upload image"
                        );
                    },
                    onFinish: () => {
                        setIsProcessing(false);
                    },
                });
            }, "image/jpeg");
        } catch (err) {
            setError("An error occurred while processing the image");
            setIsProcessing(false);
        }
    }

    return (
        <div className="modal-container">
            <div className="modal-content">
                <h2>Crop your new profile picture</h2>

                <div className="instructions">
                    <small>
                        <i className="fa-solid fa-arrows-up-down-left-right"></i>
                        Drag to move |<i className="fa-solid fa-mouse"></i>
                        Scroll to zoom
                    </small>
                </div>

                <div className="crop-container">
                    <Cropper
                        image={imageUrl}
                        crop={crop}
                        zoom={zoom}
                        aspect={1}
                        onCropChange={setCrop}
                        onZoomChange={setZoom}
                        onCropComplete={onCropComplete}
                        cropShape="round" // bikin area crop jadi bulet
                        showGrid={false} // hilangkan grid (sbnarnya bebas sih biar clean aja)
                        zoomSpeed={0.8} // zoom sensitivity bang ini
                        minZoom={1}
                        maxZoom={5}
                        restrictPosition={false} // boleh ngedrag keluar container
                        objectFit="contain" // bagaimana gambar fit dlm container
                    />
                </div>

                {error && <small className="error-text">{error}</small>}
                <button
                    type="button"
                    className="btn btn-sm btn-muted reset-btn"
                    onClick={() => {
                        setCrop({ x: 0, y: 0 });
                        setZoom(1);
                    }}
                >
                    Reset Position
                </button>

                <div className="modal-actions">
                    <button
                        type="button"
                        className="btn btn-fill btn-sm"
                        onClick={handleSave}
                        disabled={isProcessing || !croppedAreaPixels}
                    >
                        {isProcessing ? "Saving..." : "Save"}
                    </button>

                    <button
                        type="button"
                        className="btn btn-line btn-sm"
                        onClick={onClose}
                        disabled={isProcessing}
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    );
}
