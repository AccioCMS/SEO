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

        menuLinkList: [],

        languagesList: [],

        defaultLangSlug: "",

    },
    getters: {
        get_post_types(state){
            return state.postTypeList;
        },
        get_menu_links(state){
            return state.menuLinkList;
        },
        get_data(state){
            return state.data;
        },
        get_local_languages(state){
            return state.languagesList;
        },
        get_default_lang_slug(state){
            return state.defaultLangSlug;
        }
    },
    mutations: {
        setAllData(state, obj){
            state.data = obj;
        },
        setData(state, obj){
            if(obj.lang !== undefined){
                state.data[obj.group][obj.state][obj.lang] = obj.value;
            }else{
                state.data[obj.group][obj.state] = obj.value;
            }
        },
        addGroup(state, obj){
            state.data[obj.group] = obj.value;
        },
        setPostTypeList(state, postTypeList){
            state.postTypeList = postTypeList;
        },
        setPostTypeData(state, obj){
            state.data[obj.postType][obj.type] = obj.value;
        },
        setMenuLinkList(state, menuLinkList){
            state.menuLinkList = menuLinkList;
        },
        setLanguagesList(state, languagesList){
            state.languagesList = languagesList;
        },
        setDefaultLangSlug(state, defaultLangSlug){
            state.defaultLangSlug = defaultLangSlug;
        },

    }
});