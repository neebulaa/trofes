import { Link, router } from '@inertiajs/react';
import Layout from '../Layouts/Layout';
import { useState } from 'react';
import ProfileSetup from '../PagesComponent/Onboarding/ProfileSetup';
import DietaryPreferencesSetup from '../PagesComponent/Onboarding/DietaryPreferencesSetup';
import AllergiesSetup from '../PagesComponent/Onboarding/AllergiesSetup';

import '../../css/Authentication.css';
import '../../css/OnboardingCards.css';

export default function SignUp({allergies, dietary_preferences, user, user_dietary_preferences, user_allergies}) {
    const [onboardingScreen, setOnboardingScreen] = useState([
        {
            id: 1,
            rightTitle: "Resep Enak, Pilihan Sehat. Kenali Gizinya",
            rightDescription: "Nikmati hidangan lezat sambil memahami nilai gizi di baliknya. Pelajari cara memilih bahan yang lebih sehat dan membangun kebiasaan makan yang lebih baik setiap hari.",
            title: "Mau dikenal orang lebih lagi?",
            subtitle: "Isi profilmu sekarang juga",
            screen: "ProfileSetup"
        },
        {
            id: 2,
            rightTitle: "Resep Enak, Pilihan Sehat. Kenali Gizinya",
            rightDescription: "Nikmati hidangan lezat sambil memahami nilai gizi di baliknya. Pelajari cara memilih bahan yang lebih sehat dan membangun kebiasaan makan yang lebih baik setiap hari.",
            title: "Ada Preferensi makanan?",
            subtitle: "Tentukan tipe apa kamu sebagai pecinta makanan",
            screen: "DietaryPreferencesSetup"
        },
        {
            id: 3,
            rightTitle: "Resep Enak, Pilihan Sehat. Kenali Gizinya",
            rightDescription: "Nikmati hidangan lezat sambil memahami nilai gizi di baliknya. Pelajari cara memilih bahan yang lebih sehat dan membangun kebiasaan makan yang lebih baik setiap hari.",
            title: "Ada alergi makanan?",
            subtitle: "Tentukan makanan yang paling Anda hindari",
            screen: "AllergiesSetup"
        },
    ]);

    const [currentScreenIndex, setCurrentScreenIndex] = useState(0);

    function handleNextScreen(){
        if(currentScreenIndex == 2){
            router.visit('/');
            return;
        };
        setCurrentScreenIndex(prev => prev + 1);
    }

    function handlePrevScreen(){
        if(currentScreenIndex == 0) return;
        setCurrentScreenIndex(prev => prev - 1);
    }

    return (
        <section className="onboarding" id="onboarding">
            <div className="auth-left">
                <Link href="/" className="logo">
                    <img src="/assets/logo/logo-transparent.png" alt="Trofes Logo" />
                </Link>

                <h2>{onboardingScreen[currentScreenIndex]?.title}</h2>
                <p className="subtitle">{onboardingScreen[currentScreenIndex]?.subtitle}</p>

                <div className="wrapper">
                    {currentScreenIndex > 0 && (
                        <p className="prev-btn" onClick={handlePrevScreen}><i className="fa-solid fa-chevron-left"></i> Sebelumnya</p>
                    )}

                    {onboardingScreen[currentScreenIndex]?.screen === "ProfileSetup" && (
                        <ProfileSetup user={user} handleNextScreen={handleNextScreen}/>
                    )}

                    {onboardingScreen[currentScreenIndex]?.screen === "DietaryPreferencesSetup" && (
                        <DietaryPreferencesSetup dietary_preferences={dietary_preferences} handleNextScreen={handleNextScreen} user_dietary_preferences={user_dietary_preferences}/>
                    )}

                    {onboardingScreen[currentScreenIndex]?.screen === "AllergiesSetup" && (
                        <AllergiesSetup allergies={allergies} handleNextScreen={handleNextScreen} user_allergies={user_allergies}/>
                    )}
                </div>
            </div>

            <div className="auth-right">
                <div className="right-text">
                    <h1>{onboardingScreen[currentScreenIndex]?.rightTitle}</h1>
                    <p>
                        {onboardingScreen[currentScreenIndex]?.rightDescription}
                    </p>
                </div>
            </div>
        </section>
    );
}

SignUp.layout = page => <Layout children={page} />;
