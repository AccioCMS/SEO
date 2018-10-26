import Vuex from 'vuex'
import Vue from 'vue'
import Bootstrap from '../../../../../../vendor/acciocms/core/src/resources/assets/js/bootstrap-vuex';

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
                categoriesSitemap: true,
                title: '',
                description: '',
                robots: false,
            },

            tags:{
                tagsSitemap: true,
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
        },
        get_data(state){
            return state.data;
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