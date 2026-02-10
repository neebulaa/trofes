<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}"></meta>
        <style>
            #loading-screen {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: #ffffff;
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999999;
            }

            .loader {
                width: 35px;    
                height: 80px;
                position: relative;
            }

            .loader:after {
                content: "";
                position: absolute;
                inset: 0 0 20px;
                border-radius: 15px 15px 10px 10px;
                padding: 1px;
                background: repeating-linear-gradient(-45deg,#b1f5bf 0 8px,#2fb248 0 12px) content-box;
                --c: radial-gradient(farthest-side,#000 94%,#0000);
                -webkit-mask:
                linear-gradient(#0000 0 0),
                var(--c) -10px -10px,
                var(--c)  15px -14px,
                var(--c)   9px -6px,
                var(--c) -12px  9px,
                var(--c)  14px  9px,
                var(--c)  23px 27px,
                var(--c)  -8px 35px,
                var(--c)   50% 50%,
                linear-gradient(#000 0 0);
                mask:
                linear-gradient(#000 0 0),
                var(--c) -10px -10px,
                var(--c)  15px -14px,
                var(--c)   9px -6px,
                var(--c) -12px  9px,
                var(--c)  14px  9px,
                var(--c)  23px 27px,
                var(--c)  -8px 35px,
                var(--c)   50% 50%,
                linear-gradient(#0000 0 0);
                -webkit-mask-composite: destination-out;
                mask-composite: exclude,add,add,add,add,add,add,add,add;
                -webkit-mask-repeat: no-repeat;
                animation: l2 3s infinite;
            }
            .loader:before {
                content: "";
                position: absolute;
                inset: 50% calc(50% - 4px) 0;
                background: #e0a267;
                border-radius: 50px;
            }
            @keyframes l2 {
                0%   {-webkit-mask-size: auto,0 0,0 0,0 0,0 0,0 0,0 0,0 0,0 0}
                10%  {-webkit-mask-size: auto,25px 25px,0 0,0 0,0 0,0 0,0 0,0 0,0 0}
                20%  {-webkit-mask-size: auto,25px 25px,25px 25px,0 0,0 0,0 0,0 0,0 0,0 0}
                30%  {-webkit-mask-size: auto,25px 25px,25px 25px,30px 30px,0 0,0 0,0 0,0 0,0 0}
                40%  {-webkit-mask-size: auto,25px 25px,25px 25px,30px 30px,30px 30px,0 0,0 0,0 0,0 0}
                50%  {-webkit-mask-size: auto,25px 25px,25px 25px,30px 30px,30px 30px,25px 25px,0 0,0 0,0 0}
                60%  {-webkit-mask-size: auto,25px 25px,25px 25px,30px 30px,30px 30px,25px 25px,25px 25px,0 0,0 0}
                70%  {-webkit-mask-size: auto,25px 25px,25px 25px,30px 30px,30px 30px,25px 25px,25px 25px,25px 25px,0 0}
                80%,
                100% {-webkit-mask-size: auto,25px 25px,25px 25px,30px 30px,30px 30px,25px 25px,25px 25px,25px 25px,200% 200%}
            }
        </style>
        @viteReactRefresh
        @vite('resources/js/app.jsx')
        @inertiaHead
    </head>
    <body>
        <div id="loading-screen">
            <div class="loader"></div>
        </div>

        @inertia
    </body>
</html>