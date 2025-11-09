import "./bootstrap";
import "../css/app.css";

import { createApp, h } from "vue";
import { createInertiaApp, Link, router } from "@inertiajs/vue3";
import { createPinia } from "pinia";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import Swal from "sweetalert2"; 

const pinia = createPinia();

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob("./pages/**/*.vue", { eager: true });
        return pages[`./pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue)
            .component("Link", Link)
            .mount(el);

        // Times
        const LOGOUT_TIME = 5  * 1000; // 5 minutes
        const WARNING_TIME = 0.3 * 1000; // 30 seconds before logout

        let idleTimer;
        let warningTimer;
        let alertShown = false;

        function startTimers() {
            clearTimeout(idleTimer);
            clearTimeout(warningTimer);

            // Show warning 30 seconds before logout
            warningTimer = setTimeout(() => {
                if (!alertShown) {
                    alertShown = true;
                    Swal.fire({
                        title: "You're being logged out soon",
                        text: "You have been inactive. You will be logged out in 30 seconds.",
                        icon: "warning",
                        timer: WARNING_TIME,
                        timerProgressBar: true,
                        showConfirmButton: false,
                    });
                }
            }, LOGOUT_TIME - WARNING_TIME);

            // Logout exactly after LOGOUT_TIME
            idleTimer = setTimeout(() => {
                router.post("/logout");
            }, LOGOUT_TIME);
        }

        function resetActivity() {
            if (alertShown) {
                Swal.close(); 
                alertShown = false;
            }
            startTimers();
        }

        startTimers();

        // User activity resets idle detection
        window.onload = resetActivity;
        window.onmousemove = resetActivity;
        window.onkeydown = resetActivity;
        window.onclick = resetActivity;
        window.onscroll = resetActivity;
    },
});
