<template>
    <div class="col-md-12 col-sm-12 col-xs-12 seoPanelWrapper">

        <div class="navigation-seo-tabs-container">
            <ul class="navigation-seo-tabs">
                <li :class="{activeComponentLink: activeComponent == 'general'}" @click="activeComponent = 'general'"><i class="fa fa-ellipsis-v fa-2x" aria-hidden="true"></i></li>
                <li :class="{activeComponentLink: activeComponent == 'social'}" @click="activeComponent = 'social'"><i class="fa fa-share-alt fa-2x" aria-hidden="true"></i></li>
                <li :class="{activeComponentLink: activeComponent == 'advanced'}" @click="activeComponent = 'advanced'"><i class="fa fa-cog fa-2x" aria-hidden="true"></i></li>
            </ul>
        </div>

        <div class="col-xs-11" v-if="!isLoading">
            <general v-if="activeComponent == 'general'" :data="pluginData[activeLang]"></general>
            <social v-if="activeComponent == 'social'" :data="pluginData[activeLang]" :activeLang="activeLang"></social>
            <advanced v-if="activeComponent == 'advanced'" :data="pluginData[activeLang]"></advanced>
        </div>

    </div>
</template>

<style scoped>
    .seoPanelWrapper{
        margin-top:30px;
    }
    .navigation-seo-tabs-container{
        width: 40px;
        float:left;
    }
    .navigation-seo-tabs{
        margin: 0;
        padding: 0;
    }
    .navigation-seo-tabs li{
        width: 40px;
        height: 40px;
        background-color: #b5b5b521;
        list-style: none;
        margin: 0;
        padding: 0;
        text-align: center;
        border: 1px solid #EAEAEA;
        cursor: pointer;
    }
    .navigation-seo-tabs li i{
        margin-top: 12px;
        font-size: 18px;
    }
    .activeComponentLink{
        background-color: #FFF !important;
    }
</style>
<script>
    import General from './General.vue'
    import Social from './Social.vue'
    import Advanced from './Advanced.vue'

    export default{
        props: ['pluginData', 'languages', 'activeLang', 'panel'],
        components: {
            'general': General,
            'social': Social,
            'advanced': Advanced,
        },
        data(){
            return {
                activeComponent: 'general',
                usedLanguage: [],
                isLoading: true,
            }
        },
        created(){
            if(!Object.keys(this.pluginData).length){
                for(let k in this.languages){
                    if(this.pluginData[this.languages[k].slug] === undefined){
                        this.pluginData[this.languages[k].slug] = {
                            // meta
                            title: '',
                            description: '',
                            // social
                            facebookTitle: '',
                            facebookDescription: '',
                            facebookMediaID: '',
                            twitterTitle: '',
                            twitterDescription: '',
                            twitterMediaID: '',
                            //advanced
                            isIndex: true,
                            isFollow: true,
                            canonicalURL: '',
                        };
                    }
                }
            }

            if(this.$route.params.id !== undefined){
                this.$http.get(this.basePath+'/'+this.$route.params.adminPrefix+'/'+this.activeLang+'/plugins/accio/seo/details/'+this.$route.params.id+'/'+this.$route.params.post_type)
                    .then((resp) => {
                        const data = resp.body.data;
                        const media = resp.body.media;

                        if(Object.keys(data).length){
                            for(let key in data){
                                for(let langKey in this.languages){
                                    if(data[key] !== undefined && data[key] !== null) {
                                        if (this.pluginData[this.languages[langKey].slug] !== undefined && data[key][this.languages[langKey].slug] !== undefined) {
                                            this.pluginData[this.languages[langKey].slug][key] = data[key][this.languages[langKey].slug];
                                        }
                                    }
                                }
                            }

                            for(let key in media){
                                this.$store.commit('setMediaSelectedFilesNested', [key, media[key]]);
                            }
                        }

                        this.isLoading = false;
                    });
            }else{
                this.isLoading = false;
            }

        },
        computed:{
            // get base path
            basePath(){
                return this.$store.getters.get_base_path;
            },
        }
    }
</script>
