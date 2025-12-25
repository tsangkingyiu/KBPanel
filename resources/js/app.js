import './bootstrap';
import { createApp } from 'vue';

// Import components
import Dashboard from './components/Dashboard.vue';
import ProjectList from './components/ProjectList.vue';
import FileManager from './components/FileManager.vue';

const app = createApp({});

// Register components
app.component('dashboard', Dashboard);
app.component('project-list', ProjectList);
app.component('file-manager', FileManager);

app.mount('#app');
