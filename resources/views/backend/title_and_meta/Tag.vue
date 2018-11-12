<template>
    <form class="form-horizontal form-label-left" id="store">
        <div class="alert alert-success">
            <h5>Variables to use within fields</h5>
            <p>{{ titlePlaceholder }} - Will be replaced with the category title</p>
            <p>{{ sitenamePlaceholder }} - The site's name</p>
            <p>{{ pagePlaceholder }} - Will be replaced with the current page number (i.e. page 2 of 4)</p>
        </div>

        <!-- TAGS FROM INPUTS -->
        <langTabs :activeLang="activeLang" v-on:activeLangChanged="activeLang = $event"></langTabs>

        <inputs
                label="Tags"
                :title="title[activeLang]"
                :description="description[activeLang]"
                :robots="robots[activeLang]"
                slug="tags"
                :activeLang="activeLang">
        </inputs>

    </form>
</template>

<script>
    import Inputs from "../panels/TitleMeta/Inputs"
    import LangTabs from "../panels/TitleMeta/LangTabs"

    export default {
        components: {
            Inputs, LangTabs
        },

        created(){
            this.activeLang = this.getDefaultLangSlug;
        },

        data(){
            return{
                titlePlaceholder: '{{title}}',
                sitenamePlaceholder: '{{sitename}}',
                pagePlaceholder: '{{page}}',
                activeLang: '',
            }
        },
        computed: {
            getDefaultLangSlug(){
                return this.$store.getters.get_default_lang_slug;
            },
            title(){
                return this.$store.state.data.tags.title;
            },
            description() {
                return this.$store.state.data.tags.description;
            },

            robots() {
                return this.$store.state.data.tags.robots;
            }
        }
    }
</script>

<style scoped>

</style>