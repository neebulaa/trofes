export default function ProfileInfo({user, handleEditProfile}){
    function getPronouns(gender){
        if(gender === 'male') return ' - he/him'
        if(gender == 'female') return ' - she/her'
        return '';
    }

    return (
        <>
        <div className="profile-flex">
            <div className="profile-image">
                <img
                    src={ user.public_profile_image }
                    alt={`profile-${user.username}`}
                />
            </div>
            <div className="profile-name">
                <h2 className="profile-fullname">{user.full_name}</h2>
                <h3 className="profile-username">
                    {user.username}
                    {user.gender && user.gender != 'silent' ? ' - ' + getPronouns(user.gender) : ''}
                </h3>
            </div>
        </div>
        {user.bio && <p className="profile-bio">{user.bio.trim()}</p> }
        <button type="button" className="btn-full btn btn-line" onClick={handleEditProfile}>
            Edit Profile
        </button>
        
        <ul className="profile-other-infos">
            <h4>Other Info</h4>

            <li className="profile-other-info">
                <i className="fa-regular fa-envelope"></i>
                <p>{user.email}</p>
            </li>

            {
                user.phone &&
                <li className="profile-other-info">
                    <i className="fa-solid fa-phone"></i>
                    <p>{user.phone}</p>
                </li>
            }

            {
                user.birth_date &&
                <li className="profile-other-info">
                    <i className="fa-regular fa-calendar"></i>
                    <p>{user.birth_date}</p>
                </li>
            }
        </ul>

        {
            user.allergies.length > 0 &&
            <div className="profile-list">
                <h4>Allergies</h4>
                <div className="profile-list-items">
                    {
                        user.allergies.map((allergy) => (
                            <span key={allergy.allergy_id} className="profile-list-item">
                                {allergy.allergy_name}
                            </span>
                        ))
                    }
                </div>
            </div>
        }

        {
            user.dietary_preferences.length > 0 &&
            <div className="profile-list">
                <h4>Dietary Preferences</h4>
                <div className="profile-list-items">
                    {
                        user.dietary_preferences.map((dietary_preference) => (
                            <span key={dietary_preference.dietary_preference_id} className="profile-list-item">
                                {dietary_preference.diet_name}
                            </span>
                        ))
                    }
                </div>
            </div>
        }
        </>
    );
}