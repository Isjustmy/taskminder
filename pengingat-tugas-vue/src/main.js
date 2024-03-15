import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { library} from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { fas } from '@fortawesome/free-solid-svg-icons'
import { fab } from '@fortawesome/free-brands-svg-icons';
import { far } from '@fortawesome/free-regular-svg-icons';
import Toast from 'vue-toastification'
import "vue-toastification/dist/index.css";
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import VueCollapsiblePanel from '@dafcoe/vue-collapsible-panel'
import "preline/preline";
import VueAwesomePaginate from "vue-awesome-paginate";
import "vue-awesome-paginate/dist/style.css";


library.add(fas, far, fab)

import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(Toast)
app.use(VueCollapsiblePanel)
app.use(VueAwesomePaginate)

app.component("font-awesome-icon", FontAwesomeIcon)
app.component('VueDatePicker', VueDatePicker)

app.use(createPinia())
app.use(router)

app.mount('#app')
