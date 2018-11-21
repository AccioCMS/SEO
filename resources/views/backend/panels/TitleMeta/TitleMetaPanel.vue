<template>
    <div class="panel-parent">
        <langTabs :activeLang="activeLang" v-on:activeLangChanged="activeLang = $event"></langTabs>

        <inputs
                :label="label"
                :title="getVModelData('title')[activeLang]"
                :description="getVModelData('description')[activeLang]"
                :robots="getVModelData('robots')[activeLang]"
                :slug="slug"
                :activeLang="activeLang">
        </inputs>

    </div>
</template>

<script>
    import Inputs from "./Inputs"
    import LangTabs from "./LangTabs"

    export default {
        props:["item", "type"],
        components: {
            Inputs, LangTabs
        },
        created(){
            this.activeLang = this.getDefaultLangSlug;
            if(this.type == "menuLink"){
                this.slug = this.item.settingsKey;
                this.label = this.item.label;
            }else{
                this.slug = this.item.slug;
                this.label = this.item.name;
            }
        },

        data(){
            return {
                activeLang: '',
                label: '',
                slug: '',
            }
        },

        methods:{
            /**
             * Get data stored in vuex
             *
             * @param type
             * @returns {*}
             */
            getVModelData(type){
                return this.$store.state.data[this.slug][type];
            }
        },

        computed:{
            getDefaultLangSlug(){
                return this.$store.getters.get_default_lang_slug;
            }
        }
    }
</script>

<style scoped>

</style>