import { Component, State } from 'src/core/shopware';
import CriteriaFactory from 'src/core/factory/criteria.factory';
import utils from 'src/core/service/util.service';
import template from './sw-sidebar-media-item.html.twig';
import './sw-sidebar-media-item.less';

Component.register('sw-sidebar-media-item', {
    template,

    props: {
        catalogId: {
            type: String,
            required: true
        }
    },

    data() {
        return {
            isLoading: true,
            catalog: null,
            mediaItems: [],
            page: 1,
            limit: 25,
            total: 0,
            term: ''
        };
    },

    computed: {
        catalogStore() {
            return State.getStore('catalog');
        },

        mediaStore() {
            return State.getStore('media');
        },

        showMore() {
            return this.itemsLoaded < this.total;
        },

        itemsLoaded() {
            return this.mediaItems.length;
        }
    },

    watch: {
        catalogId(newCatalogId) {
            this.catalogId = newCatalogId;
            this.initializeContent();
        }
    },

    created() {
        this.componentCreated();
    },

    methods: {
        componentCreated() {
            this.initializeContent();
        },

        initializeContent() {
            this.page = 1;
            this.term = '';
            this.mediaItems = [];

            if (this.catalogId) {
                this.catalog = this.catalogStore.getById(this.catalogId);
            } else {
                this.catalog = null;
            }

            this.getList();
        },

        onSearchInput(searchTopic) {
            this.doListSearch(searchTopic);
        },

        doListSearch: utils.debounce(function debouncedSearch(searchTopic) {
            const searchTerm = searchTopic || '';
            this.term = searchTerm.trim();
            this.page = 1;
            this.getList();
        }, 400),

        handleMediaGridItemDelete() {
            const pages = this.page;
            this.page = 1;
            this.getList().then(() => {
                while (this.page < pages) {
                    this.page += 1;
                    this.extendList();
                }
            });
        },

        addItemToProduct(item) {
            this.$emit('sw-sidebar-media-item-add-item-to-product', item);
        },

        onLoadMore() {
            this.page += 1;
            this.extendList();
        },

        extendList() {
            const params = this.getListingParams();
            params.criteria = CriteriaFactory.equals('catalogId', this.catalog.id);

            return this.mediaStore.getList(params).then((response) => {
                this.mediaItems = this.mediaItems.concat(response.items);
                return this.mediaItems;
            });
        },

        getList() {
            this.isLoading = true;

            if (!this.catalog) {
                return new Promise(() => {
                    this.mediaItems = [];
                    this.total = 0;
                    this.isLoading = false;
                    return this.mediaItems;
                });
            }

            const params = this.getListingParams();
            params.criteria = CriteriaFactory.equals('catalogId', this.catalog.id);

            return this.mediaStore.getList(params).then((response) => {
                this.mediaItems = response.items;
                this.total = response.total;
                this.isLoading = false;

                return this.mediaItems;
            });
        },

        getListingParams() {
            const params = {
                limit: this.limit,
                page: this.page
            };

            if (this.term && this.term.length) {
                params.term = this.term;
            }

            return params;
        }
    }
});