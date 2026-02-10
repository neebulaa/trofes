import { useEffect, useRef, useState } from "react";

export default function CameraCaptureModal({ onCapture, onClose }) {
    const videoRef = useRef(null);
    const streamRef = useRef(null);
    const [error, setError] = useState(null);

    useEffect(() => {
        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: "user" },
                });
                streamRef.current = stream;
                if (videoRef.current) videoRef.current.srcObject = stream;
            } catch (err) {
                setError("Unable to access camera");
            }
        }

        startCamera();

        return () => {
            streamRef.current?.getTracks().forEach((t) => t.stop());
        };
    }, []);

    function takePhoto(e) {
        e?.preventDefault();
        const video = videoRef.current;
        if (!video) return;

        const canvas = document.createElement("canvas");
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;

        const ctx = canvas.getContext("2d");
        ctx.drawImage(video, 0, 0);

        canvas.toBlob((blob) => {
            if (!blob) return;

            const file = new File([blob], "camera-photo.jpg", {
                type: "image/jpeg",
            });

            // send the file upward to open UploadImageModal
            onCapture(file);

            // close camera modal (stops stream in cleanup)
            onClose();
        }, "image/jpeg");
    }

    return (
        <div className="modal-container">
            <div className="modal-content">
                <h2 className="mb-1">Take a Photo</h2>

                {error ? (
                    <small className="error-text">{error}</small>
                ) : (
                    <video
                        style={{ width: '100%' }}
                        ref={videoRef}
                        autoPlay
                        playsInline
                        className="camera-preview"
                    />
                )}

                <div className="modal-actions">
                    <button
                        type="button"
                        className="btn btn-fill btn-sm"
                        onClick={takePhoto}
                        disabled={!!error}
                    >
                        Capture
                    </button>

                    <button
                        type="button"
                        className="btn btn-line btn-sm"
                        onClick={(e) => {
                            e.preventDefault();
                            onClose();
                        }}
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    );
}
