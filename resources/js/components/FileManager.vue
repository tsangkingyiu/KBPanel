<template>
    <div class="file-manager">
        <div class="toolbar">
            <button @click="uploadFile" class="btn-primary">Upload</button>
            <button @click="createFolder" class="btn-secondary">New Folder</button>
        </div>
        <div class="file-tree">
            <div v-for="file in files" :key="file.path" class="file-item" @click="selectFile(file)">
                <span class="icon">{{ file.type === 'dir' ? '📁' : '📄' }}</span>
                <span>{{ file.name }}</span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'FileManager',
    props: ['projectId'],
    data() {
        return {
            files: [],
            currentPath: '/'
        }
    },
    mounted() {
        this.loadFiles();
    },
    methods: {
        loadFiles() {
            axios.get(`/api/projects/${this.projectId}/files?path=${this.currentPath}`)
                .then(response => {
                    this.files = response.data;
                })
                .catch(error => {
                    console.error('Failed to load files:', error);
                });
        },
        selectFile(file) {
            if (file.type === 'dir') {
                this.currentPath = file.path;
                this.loadFiles();
            } else {
                // TODO: Open file editor
                console.log('Open file:', file.path);
            }
        },
        uploadFile() {
            // TODO: Implement file upload
            console.log('Upload file');
        },
        createFolder() {
            // TODO: Implement folder creation
            console.log('Create folder');
        }
    }
}
</script>

<style scoped>
.toolbar {
    margin-bottom: 1rem;
}

.file-tree {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
}

.file-item {
    padding: 0.5rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.file-item:hover {
    background-color: #f3f4f6;
}
</style>
