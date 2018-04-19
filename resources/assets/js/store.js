import Vuex from 'vuex'
import Vue from 'vue'
import Bootstrap from './bootstrap';

Vue.use(Vuex);

export const store = new Vuex.Store({
    modules: {
        Bootstrap
    },
    state: {
        data: {
            sitemap:{
                isActive: false,
                maxEntriesAllowed: '',
            },

            post: {
                postTypesInSitemap: [],
                postsIgnoredInSitemap: {},
            },

            categories: {
                postTypesInSitemap: [],
                categoriesIgnoredInSitemap: {},
                title: '',
                description: '',
                robots: false,
            },

            tags:{
                postTypesInSitemap: [],
                tagsIgnoredInSitemap: {},
                title: '',
                description: '',
                robots: false,
            },

            users:{
                authorSitemap: true,
                title: '',
                description: '',
                robots: false,
            },


            robots:{
                content: '',
            },

            redirectManager:{
                content: '',
            }
        },

        postTypesList: [],
    },
    getters: {
        get_post_types(state){
            return state.postTypeList;
        }
    },
    mutations: {
        setData(state, obj){
            state.data[obj.group][obj.state] = obj.value;
        },
        addGroup(state, obj){
            state.data[obj.group] = obj.value;
        },
        setPostTypeList(state, postTypeList){
            state.postTypeList = postTypeList;
        },
        setPostTypeData(state, obj){
            state.data[obj.postType][obj.type] = obj.value;
        }

    }
});