import { createBrowserRouter } from "react-router-dom";
import App from "../App";
import Home from "../pages/Home";
// import Home from "../pages/Home";
// import About from "../pages/About";

const router = createBrowserRouter([
    {
        path: "/",
        element: <App />,
        // children: [
        //     { path: "", element: <Home /> },
        //     { path: "about", element: <About /> },
        // ],
    },
    {
        path: "/home",
        element: <Home />,
    },
]);

export default router;
