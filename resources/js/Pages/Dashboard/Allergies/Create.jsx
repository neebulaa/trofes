import { useEffect, useRef, useState } from "react";
import DashboardLayout from "../../../Layouts/DashboardLayout";
import { Link, useForm } from "@inertiajs/react";
import DashboardIcon from "../../../Components/Dashboard/DashboardIcon";

export default function CreateAllergy() {
    const { data, setData, post, processing, errors } = useForm({
        allergy_name: "",
        examples: "",
        image: null,
    });

    const fileRef = useRef(null);
    const [preview, setPreview] = useState(null);

    useEffect(() => {
        if (!data.image) {
            setPreview(null);
            return;
        }

        const url = URL.createObjectURL(data.image);
        setPreview(url);

        return () => URL.revokeObjectURL(url);
    }, [data.image]);

    const submit = (e) => {
        e.preventDefault();
        post("/dashboard/allergies", { forceFormData: true });
    };

    const removeImage = () => {
        setData("image", null);
        setPreview(null);

        if (fileRef.current) fileRef.current.value = "";
    };

    return (
        <DashboardLayout title="Dashboard - Allergies">
            <div className="crud-header">
                <Link href="/dashboard/allergies" aria-label="Back">
                    <DashboardIcon name="chevronLeft" />
                </Link>
                <div>
                    <h1 className="crud-title">Create Allergy</h1>
                    <p className="text-muted">Publish a new nutrition allergy.</p>
                </div>
            </div>

            <form onSubmit={submit} className="mt-1 crud-form">
                <div className="input-group">
                    <label htmlFor="allergy_name">Allergy Name</label>
                    <input
                        id="allergy_name"
                        type="text"
                        value={data.allergy_name}
                        onChange={(e) => setData("allergy_name", e.target.value)}
                        placeholder="Enter allergy name"
                    />
                    {errors.allergy_name && (
                        <small className="error-text">{errors.allergy_name}</small>
                    )}
                </div>

                <div className="input-group">
                    <label>Examples</label>
                    <input
                        id="examples"
                        type="text"
                        value={data.examples}
                        onChange={(e) => setData("examples", e.target.value)}
                        placeholder="Enter examples"
                    />
                    {errors.examples && (
                        <small className="error-text">{errors.examples}</small>
                    )}
                </div>

                <div className="input-group">
                    <label>Image</label>

                    <input
                        ref={fileRef}
                        id="image"
                        type="file"
                        accept="image/*"
                        className="file-input-hidden"
                        onChange={(e) =>
                            setData("image", e.target.files?.[0] ?? null)
                        }
                    />

                    <label htmlFor="image" className="file-input-trigger">
                        <i className="fa-regular fa-image"></i>
                        <span className="ml-05">
                            {data.image ? "Change image" : "Choose image"}
                        </span>
                    </label>

                    {data.image && (
                        <div className="file-input-filename">
                            {data.image.name}
                        </div>
                    )}

                    {errors.image && !data.image && (
                        <>
                            <br />
                            <small className="error-text">{errors.image}</small>
                        </>
                    )}

                    {preview && (
                        <div className="image-preview mt-05 image-preview-contain">
                            <img src={preview} alt="Preview" />
                            <button
                                type="button"
                                className="image-remove"
                                onClick={removeImage}
                                aria-label="Remove image"
                                title="Remove image"
                            >
                                <i className="fa-solid fa-xmark" />
                            </button>
                        </div>
                    )}
                </div>

                <button
                    type="submit"
                    className="btn btn-fill mt-1"
                    disabled={processing}
                >
                    <span>
                        {processing ? "Publishing..." : "Publish Guide"}
                    </span>
                </button>
            </form>
        </DashboardLayout>
    );
}
