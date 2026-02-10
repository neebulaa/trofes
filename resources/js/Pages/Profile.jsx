import Layout from '../Layouts/Layout'
import { Link } from '@inertiajs/react';
import RecipeCard from '../Components/RecipeCard';
import ProfileInfo from '../PagesComponent/Profile/ProfileInfo';
import ProfileEditForm from '../PagesComponent/Profile/ProfileEditForm';
import FlashMessage from '../Components/FlashMessage';
import { router } from "@inertiajs/react";

import '../../css/Profile.css';
import { useState } from 'react';
import Paginator from '../Components/Paginator';

export default function Profile({user, liked_recipes, allergies, dietary_preferences}){
    const [onEdit, setOnEdit] = useState(false);

    function handleEditProfile(){
        setOnEdit(!onEdit);
    }

    return (
        <>
        <FlashMessage className="mb-1 flash-screen"/>
        <section className="profile" id="profile">
            <div className="container">
                <div className="profile-left">
                    {
                        onEdit ?
                        <ProfileEditForm user={user} allergies={allergies} dietary_preferences={dietary_preferences} handleEditProfile={handleEditProfile} />
                        : <ProfileInfo user={user} handleEditProfile={handleEditProfile} />
                    }
                </div>

                <div className="profile-right">
                    <h2 className="profile-right-title">Liked Recipes</h2>
                    {
                        liked_recipes.data.length > 0 &&
                        <p className='mb-05 liked-recipes-count'>Liked {liked_recipes.data.length} recipes</p>
                    }

                    <div className={"profile-liked-recipes-container " + (liked_recipes.data.length > 0 ? ' recipes-container' : '')}>
                        {
                            liked_recipes.data.length > 0 ? (
                                liked_recipes.data.map((recipe) => (
                                    <RecipeCard key={recipe.recipe_id} recipe={recipe} />
                                ))
                            ) : (
                                <>
                                    <p className="mb-05">You haven't liked any recipes yet. Explore and like your favorite recipes!</p>
                                    <Link className="btn btn-fill btn-sm mt-05" href="/recipes">Explore Recipes</Link>
                                </>
                            )
                        }
                    </div>

                    {liked_recipes.data.length > 0 &&
                        <Paginator paginator={liked_recipes} onNavigate={(url) => router.get(url)}/>
                    }
                </div>
            </div>
        </section>
        </>
    )
}

Profile.layout = page => <Layout children={page} />