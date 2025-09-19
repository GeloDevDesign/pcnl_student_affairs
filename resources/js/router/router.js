import { createRouter, createWebHistory } from "vue-router";

import welcome from "../pages/dashboard/welcome.vue";
import event from "../pages/dashboard/event.vue";
import handBook from "../pages/dashboard/hand-book.vue";


const routes = [
    {
        path: "/dashboard",
        children: [
            {
                path: "welcome",
                component: welcome,
            },
            {
                path: "event",
                component: event,
            },
            {
                path: "hand-book",
                component: handBook,
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
