<template>
    <form class="form-horizontal form-label-left" id="store">
        <div class="alert alert-success">
            <h5>Variables to use within fields</h5>
            <p>{{ titlePlaceholder }} - Will be replaced with the title of the post/page</p>
            <p>{{ sitenamePlaceholder }} - The site's name</p>
            <p>{{ pagePlaceholder }} - Will be replaced with the current page number (i.e. page 2 of 4)</p>
        </div>

        <titleMetaPanel
                v-for="(menuLink, index) in get_menu_links"
                :key="index"
                :item="menuLink"
                type="menuLink">
        </titleMetaPanel>

    </form>
</template>

<script>
    import TitleMetaPanel from "../panels/TitleMeta/TitleMetaPanel"

    export default {
        components: {
            "titleMetaPanel" : TitleMetaPanel
        },

        data(){
            return{
                titlePlaceholder: '{{title}}',
                sitenamePlaceholder: '{{sitename}}',
                pagePlaceholder: '{{page}}',
                activeLang: '',
            }
        },
        methods:{
            dataChanged(event, menuLinkKey, type){
                this.$store.commit('setMenuLinkData', {menuLink: menuLinkKey, type: type, value: event.target.value});
            },
            changeBoolean(menuLinkKey, key, value){
                this.$store.commit('setData', {group: menuLinkKey, state: key, value: value});
            }
        },
        computed: {
            get_menu_links(){
                return this.$store.getters.get_menu_links;
            }
        }
    }
</script>