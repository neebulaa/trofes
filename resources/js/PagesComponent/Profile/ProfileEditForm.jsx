import { useForm } from "@inertiajs/react";
import { useState } from "react";
import CustomDatalist from "../../Components/CustomDatalist";
import EditProfileDropdown from "./EditProfileDropdown";
import UploadImageModal from "./UploadImageModal";
import CameraCaptureModal from "../../Components/CameraCaptureModal";

export default function ProfileEditForm({
    user,
    handleEditProfile,
    allergies,
    dietary_preferences,
}) {
    const {
        data,
        setData,
        put,
        delete: destroy,
        processing,
        errors,
    } = useForm({
        full_name: user.full_name || "",
        username: user.username || "",
        gender: user.gender || "",
        bio: user.bio || "",
        email: user.email || "",
        phone: user.phone || "",
        birth_date: user.birth_date || "",
        allergies: user.allergies?.map((a) => a.allergy_id) || [],
        dietary_preferences:
            user.dietary_preferences?.map((d) => d.dietary_preference_id) || [],
    });

    const [selectedAllergies, setSelectedAllergies] = useState(
        user.allergies?.map((a) => ({
            value: a.allergy_id,
            label: a.allergy_name,
        })) || []
    );

    const [selectedDietaryPreferences, setSelectedDietaryPreferences] =
        useState(
            user.dietary_preferences?.map((d) => ({
                value: d.dietary_preference_id,
                label: d.diet_name,
            })) || []
        );

    function handleDietaryPreferencesChange(newValues) {
        setSelectedDietaryPreferences(newValues);
        setData(
            "dietary_preferences",
            newValues.map((v) => v.value)
        );
    }

    function handleAllergyChange(newValues) {
        setSelectedAllergies(newValues);
        setData(
            "allergies",
            newValues.map((v) => v.value)
        );
    }

    const [imgSrc, setImgSrc] = useState(user.public_profile_image || null);
    const [rawImage, setRawImage] = useState(null);
    const [openModal, setOpenModal] = useState(false);
    const [showCamera, setShowCamera] = useState(false);

    function handleDesktopCamera() {
        setShowCamera(true);
    }

    function handleCapturedFile(file) {
        handleImageSelected(file);
        setShowCamera(false);
    }

    function handleCameraCapture(file) {
        setRawImage(file);
        setOpenModal(true);
    }

    function handleImageSelected(file) {
        setRawImage(file);
        setOpenModal(true);
    }

    function handleRemoveImage() {
        setImgSrc(null);
        setRawImage(null);

        destroy("/profile/remove-profile-image", {
            onSuccess: () => {
                setImgSrc(null);
            },
        });
    }

    function handleSubmit(e) {
        e.preventDefault();

        put("/profile/update", {
            onSuccess: handleEditProfile,
        });
    }

    return (
        <form onSubmit={handleSubmit}>
            <div className="profile-image-container">
                <div className="profile-image">
                    <img
                        src={user.public_profile_image}
                        alt={`profile-${user.username}`}
                    />
                </div>

                <EditProfileDropdown
                    currentImage={imgSrc}
                    onUpload={handleImageSelected}
                    onRemove={handleRemoveImage}
                    onTakePhotoDesktop={handleDesktopCamera}
                />

                {openModal && (
                    <UploadImageModal
                        file={rawImage}
                        onSaved={handleCapturedFile}
                        onClose={() => setOpenModal(false)}
                    />
                )}

                {showCamera && (
                    <CameraCaptureModal
                        onCapture={handleCameraCapture}
                        onClose={() => setShowCamera(false)}
                    />
                )}
            </div>

            <div className="input-group input-sm">
                <label>Full Name</label>
                <input
                    value={data.full_name}
                    onChange={(e) => setData("full_name", e.target.value)}
                />
                {errors.full_name && (
                    <small className="error-text">{errors.full_name}</small>
                )}
            </div>

            <div className="input-group input-sm">
                <label>Username</label>
                <input
                    value={data.username}
                    onChange={(e) => setData("username", e.target.value)}
                />
                {errors.username && (
                    <small className="error-text">{errors.username}</small>
                )}
            </div>

            <div className="input-group input-sm">
                <label>Jenis Kelamin</label>
                <select
                    value={data.gender}
                    onChange={(e) => setData("gender", e.target.value)}
                >
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="silent">Prefer not to say</option>
                </select>
                {errors.gender && (
                    <small className="error-text">{errors.gender}</small>
                )}
            </div>

            <div className="input-group input-sm">
                <label>Bio</label>
                <textarea
                    value={data.bio}
                    onChange={(e) => setData("bio", e.target.value)}
                />
                {errors.bio && (
                    <small className="error-text">{errors.bio}</small>
                )}
            </div>

            <div className="input-group input-sm">
                <label>Email</label>
                <input
                    value={data.email}
                    onChange={(e) => setData("email", e.target.value)}
                    // disabled={true}
                    // readOnly={true}
                />
                {errors.email && (
                    <small className="error-text">{errors.email}</small>
                )}
            </div>

            <div className="input-group input-sm">
                <label>Phone</label>
                <div className="nohp-input">
                    <div className="phone-identifier">+62</div>
                    <input
                        value={data.phone}
                        onChange={(e) => setData("phone", e.target.value)}
                    />
                </div>
                {errors.phone && (
                    <small className="error-text">{errors.phone}</small>
                )}
            </div>

            <div className="input-group input-sm">
                <label>Date of Birth</label>
                <input
                    // readOnly={true}
                    // disabled={true}
                    type="date"
                    value={data.birth_date}
                    onChange={(e) => setData("birth_date", e.target.value)}
                />
                {errors.birth_date && (
                    <small className="error-text">{errors.birth_date}</small>
                )}
            </div>

            <CustomDatalist
                className="input-sm"
                label="Select Allergies"
                options={allergies.map((a) => ({
                    value: a.allergy_id,
                    label: a.allergy_name,
                }))}
                value={selectedAllergies}
                onChange={handleAllergyChange}
                placeholder="Type allergy..."
            />

            <CustomDatalist
                className="input-sm"
                label="Select Dietary Preferences"
                options={dietary_preferences.map((a) => ({
                    value: a.dietary_preference_id,
                    label: a.diet_name,
                }))}
                value={selectedDietaryPreferences}
                onChange={handleDietaryPreferencesChange}
                placeholder="Type dietary preference..."
            />

            <div className="edit-profile-btns">
                <button
                    type="submit"
                    className="btn-full btn btn-fill btn-sm"
                    disabled={processing}
                >
                    Save Profile
                </button>

                <button
                    type="button"
                    className="btn-sm btn-full btn btn-line mr-05"
                    onClick={handleEditProfile}
                >
                    Cancel
                </button>
            </div>
        </form>
    );
}
