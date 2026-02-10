import { useEffect, useRef, useState } from "react";
import DashboardLayout from "../../../Layouts/DashboardLayout";
import { Link, useForm } from "@inertiajs/react";
import DashboardIcon from "../../../Components/Dashboard/DashboardIcon";

export default function EditAllergy({ allergy }) {
    const { data, setData, post, processing, errors, clearErrors } = useForm({
        _method: "put",
        allergy_name: allergy.allergy_name ?? "",
        examples: allergy.examples ?? "",
        image: null,
        image_removed: "0",
    });

    const fileRef = useRef(null);
    const [preview, setPreview] = useState(allergy.public_image ?? null);

    useEffect(() => {
        if (!data.image) return;

        const url = URL.createObjectURL(data.image);
        setPreview(url);
        setData("image_removed", "0");

        return () => URL.revokeObjectURL(url);
    }, [data.image]);

    const removeImage = () => {
        setData("image", null);
        setData("image_removed", "1");
        setPreview(null);

        if (fileRef.current) fileRef.current.value = "";
    };

    const submit = (e) => {
        e.preventDefault();
        clearErrors();

        post(`/dashboard/allergies/${allergy.allergy_code}`, {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    return (
        <DashboardLayout title="Dashboard - Edit Allergy">
            <div className="crud-header">
                <Link href="/dashboard/allergies" aria-label="Back">
                    <DashboardIcon name="chevronLeft" />
                </Link>
                <div>
                    <h1 className="crud-title">Edit Allergy</h1>
                    <p className="text-muted">Update existing allergy.</p>
                </div>
            </div>

            <form onSubmit={submit} className="mt-1 crud-form">
                <div className="input-group">
                    <label htmlFor="allergy_name">Allergy Name</label>
                    <input
                        id="allergy_name"
                        type="text"
                        value={data.allergy_name}
                        onChange={(e) =>
                            setData("allergy_name", e.target.value)
                        }
                        placeholder="Enter allergy name"
                    />
                    {errors.allergy_name && (
                        <small className="error-text">
                            {errors.allergy_name}
                        </small>
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
                            {preview ? "Change image" : "Choose image"}
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
                    <span>{processing ? "Saving..." : "Update Allergy"}</span>
                </button>
            </form>
        </DashboardLayout>
    );
}
