<script setup>
import Layout from "../Shared/Layout.vue";

defineProps({ user: Object });

const alertAction = (message) => {
    alert(message);
};
</script>

<template>
    <Layout>
        <h1>Welcome</h1>
        <p>
            Hello {{ user.name }} {{ user.surname }}, welcome to your first
            Inertia app!
        </p>
        <button
            @click="alertAction('this is a message')"
            class="btn btn-primary"
        >
            Click Me
        </button>
    </Layout>
</template>
