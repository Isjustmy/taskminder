import { createApp } from 'vue';
import { useToast } from 'vue-toastification';

const app = createApp({});
const toast = useToast();
app.provide('toast', toast);
