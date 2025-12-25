<template>
    <div class="project-list">
        <div class="header">
            <h2>Projects</h2>
            <button @click="createProject" class="btn-primary">New Project</button>
        </div>
        <table class="projects-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Domain</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="project in projects" :key="project.id">
                    <td>{{ project.name }}</td>
                    <td>{{ project.domain }}</td>
                    <td>{{ project.type }}</td>
                    <td>
                        <span :class="`status-badge status-${project.status}`">
                            {{ project.status }}
                        </span>
                    </td>
                    <td>
                        <button @click="editProject(project.id)" class="btn-sm">Edit</button>
                        <button @click="deleteProject(project.id)" class="btn-sm btn-danger">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    name: 'ProjectList',
    data() {
        return {
            projects: []
        }
    },
    mounted() {
        this.loadProjects();
    },
    methods: {
        loadProjects() {
            axios.get('/api/projects')
                .then(response => {
                    this.projects = response.data;
                })
                .catch(error => {
                    console.error('Failed to load projects:', error);
                });
        },
        createProject() {
            // TODO: Implement create project modal
            console.log('Create project');
        },
        editProject(id) {
            // TODO: Implement edit project
            console.log('Edit project:', id);
        },
        deleteProject(id) {
            if (confirm('Are you sure you want to delete this project?')) {
                axios.delete(`/api/projects/${id}`)
                    .then(() => {
                        this.loadProjects();
                    })
                    .catch(error => {
                        console.error('Failed to delete project:', error);
                    });
            }
        }
    }
}
</script>

<style scoped>
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.projects-table {
    width: 100%;
    border-collapse: collapse;
}

.projects-table th,
.projects-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.875rem;
}

.status-active {
    background-color: #dcfce7;
    color: #166534;
}

.status-suspended {
    background-color: #fee2e2;
    color: #991b1b;
}
</style>
