/**
 * *****************************************
 *  BOOTSTRAP VUEX MODULE
 *  ****************************************
 *  Basic states that vuex must have
 *  NOTE: plugin won't work without them
 */

export default {
    state: {
        baseURL: '',
        basePath: '',
        global_data: [],
        pluginsConfigs: [],
        labels: {},
        openModule: '',
        auth: {
            user: {},
        },
        menuMode: '',
        menuLinkList: {},
    },
    getters: {
        get_base_url(state){
            return state.base_url;
        },
        get_base_path(state){
            return state.base_path;
        },
        get_global_data(state){
            return state.global_data;
        },
        get_plugins_configs(state){
            return state.pluginsConfigs;
        },
        get_labels(state){
            return state.labels;
        },
        get_auth(state){
            return state.auth;
        },
        get_menu_mode(state){
            return state.menuMode;
        },
        get_menu_link_list(state){
            return state.menuLinkList;
        },
        get_open_module(state){
            return state.openModule;
        },
    },
    mutations: {
        setLabels(state, labels){
            state.labels = labels;
        },
        setGlobalData(state, global_data){
            state.global_data = global_data;
        },
        setPluginsConfigs(state, pluginsConfigs){
            state.pluginsConfigs = pluginsConfigs;
        },
        setBaseURL(state, base_url){
            state.base_url = base_url;
        },
        setBasePath(state, base_path){
            state.base_path = base_path;
        },
        setAuth(state, auth){
            state.auth = auth;
        },
        setMenuMode(state, menuMode){
            state.menuMode = menuMode;
        },
        setMenuLinkList(state, menuLinkList){
            state.menuLinkList = menuLinkList;
        },
        addItemToMenuLinkList(state, item){
            state.menuLinkList[item.key] = item.value;
        },
        setOpenModule(state, openModule){
            state.openModule = openModule;
        },
    },
    actions: {}
};