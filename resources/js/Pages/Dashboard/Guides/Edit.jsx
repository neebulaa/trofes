import { useEffect, useRef, useState } from "react";
import DashboardLayout from "../../../Layouts/DashboardLayout";
import { Link, useForm } from "@inertiajs/react";
import DashboardIcon from "../../../Components/Dashboard/DashboardIcon";
import RichTextEditor from "../../../Components/RichTextEditor";

export default function EditGuide({ guide }) {
    const { data, setData, post, processing, errors, clearErrors } = useForm({
        _method: "put",
        title: guide.title ?? "",
        content: guide.content ?? "",
        image: null,
        image_removed: "0",
    });

    const fileRef = useRef(null);
    const [preview, setPreview] = useState(guide.public_image ?? null);

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

        post(`/dashboard/guides/${guide.slug}`, {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    return (
        <DashboardLayout title="Dashboard - Edit Guide">
            <div className="crud-header">
                <Link href="/dashboard/guides" aria-label="Back">
                    <DashboardIcon name="chevronLeft" />
                </Link>
                <div>
                    <h1 className="crud-title">Edit Guide</h1>
                    <p className="text-muted">
                        Update existing nutrition guide.
                    </p>
                </div>
            </div>

            <form onSubmit={submit} className="mt-1 crud-form">
                <div className="input-group">
                    <label htmlFor="title">Title</label>
                    <input
                        id="title"
                        type="text"
                        value={data.title}
                        onChange={(e) => setData("title", e.target.value)}
                        placeholder="Enter guide title"
                    />
                    {errors.title && (
                        <small className="error-text">{errors.title}</small>
                    )}
                </div>

                <div className="input-group">
                    <label>Content</label>
                    <RichTextEditor
                        initialHTML={guide.content}
                        onChange={(html) => setData("content", html)}
                        placeholder="Write your guide content..."
                    />
                    {errors.content && (
                        <small className="error-text">{errors.content}</small>
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

                    {errors.image && (
                        <>
                            <br />
                            <small className="error-text">{errors.image}</small>
                        </>
                    )}

                    {preview && (
                        <div className="image-preview mt-05">
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
                        {processing ? "Saving..." : "Update Guide"}
                    </span>
                </button>
            </form>
        </DashboardLayout>
    );
}
