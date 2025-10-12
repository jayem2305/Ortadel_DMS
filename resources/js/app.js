import { createApp } from "vue";
import { createPinia } from "pinia";
import router from "./router";
import App from "./App.vue";

// Import bootstrap for axios configuration
import "./bootstrap";

// Import permission directives
import { permission, permissionAll, permissionModule } from "./directives/permission";

const app = createApp(App);

// Install Pinia
const pinia = createPinia();
app.use(pinia);

// Install router
app.use(router);

// Register permission directives
app.directive('permission', permission);
app.directive('permission-all', permissionAll);
app.directive('permission-module', permissionModule);

// Mount app
app.mount("#app");
