import Layout from "../Layouts/Layout";
import Hero from "../PagesComponent/Home/Hero";
import About from "../PagesComponent/Home/About";
import Guides from "../PagesComponent/Home/Guides";
import RecommendedRecipes from "../PagesComponent/Home/RecommendedRecipes";
import '../../css/Home.css'
import { usePage } from "@inertiajs/react";

export default function Home({guides, recipes, recommended_recipes = []}) {
    const {auth: {user}} = usePage().props;
    return (
        <>
            <Hero recipes={recipes} />
            {
                user && (
                    <RecommendedRecipes recipes={recommended_recipes}/>
                )
            }
            <About />
            <Guides guides={guides} />
        </>
    );
}

Home.layout = page => <Layout children={page}/>