import { Component, State } from 'src/core/shopware';
import template from './sw-media-breadcrumbs.html.twig';
import './sw-media-breadcrumbs.scss';

Component.register('sw-media-breadcrumbs', {
    template,

    model: {
        prop: 'currentFolderId',
        event: 'media-folder-changed'
    },

    props: {
        currentFolderId: {
            type: String,
            required: false,
            default: null
        },

        small: {
            type: Boolean,
            required: false,
            default: false
        }
    },

    data() {
        return {
            currentFolder: null
        };
    },

    computed: {
        mediaFolderStore() {
            return State.getStore('media_folder');
        },

        rootFolder() {
            const root = new this.mediaFolderStore.EntityClass(this.mediaFolderStore.entityName, null, null, null);
            root.name = this.$tc('sw-media.index.rootFolderName');

            return root;
        },

        parentFolder() {
            if (!this.currentFolder || this.currentFolder === this.rootFolder) {
                return null;
            }

            if (!this.currentFolder.parentId) {
                return this.rootFolder;
            }

            return this.mediaFolderStore.getById(this.currentFolder.parentId);
        },

        swMediaBreadcrumbsClasses() {
            return {
                'is--small': this.small
            };
        }
    },

    watch: {
        currentFolderId() {
            this.updateFolder();
        }
    },

    created() {
        this.updateFolder();
    },

    methods: {
        updateFolder() {
            if (!this.currentFolderId) {
                this.currentFolder = this.rootFolder;
                return;
            }
            this.currentFolder = this.mediaFolderStore.getById(this.currentFolderId);
        },

        onBreadcrumbsItemClicked(id) {
            this.$emit('media-folder-changed', id);
        }
    }
});