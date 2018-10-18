<template>
    <div class="col-lg-12 col-md-12">
        <span style="display:none">{{ isMediaOpen }}</span>

        <ul class="navigation-seo-social-tabs">
            <li @click="activeTab = 'facebook'" :class="{activeLink: activeTab == 'facebook'}"><i class="fa fa-facebook" aria-hidden="true"></i></li>
            <li @click="activeTab = 'twitter'" :class="{activeLink: activeTab == 'twitter'}"><i class="fa fa-twitter" aria-hidden="true"></i></li>
        </ul>

        <div class="row">
            <div class="facebook" v-if="activeTab == 'facebook'">
                <!-- facebook title -->
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-2 col-xs-12">Facebook Title: </label>
                    <div class="col-md-10 col-sm-10 col-xs-12">
                        <input type="text" class="form-control" v-model="data.facebookTitle">
                    </div>
                </div>

                <!-- facebook description -->
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-2 col-xs-12">Facebook Description: </label>
                    <div class="col-md-10 col-sm-10 col-xs-12">
                        <input type="text" class="form-control" v-model="data.facebookDescription">
                    </div>
                </div>

                <!-- Facebook image -->
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-2 col-xs-12">Facebook Image: </label>
                    <div class="imagePrevContainer col-md-10 col-sm-10 col-xs-12">
                        <div class="imageSingleThumb" v-for="(file, count) in mediaSelectedFiles['plugin_accio_seo_facebook_image_'+activeLang]" :key="count">
                            <i class="fa fa-close closeBtnForPrevImages" @click="removeImage('plugin_accio_seo_facebook_image_')"></i>
                            <img :src="constructMediaUrl(file)">
                        </div>

                        <div class="clearfix"></div>

                        <a class="btn btn-info" @click="openMedia('plugin_accio_seo_facebook_image_'+activeLang, 'image')" v-if="mediaSelectedFiles['plugin_accio_seo_facebook_image_'+activeLang]">
                            Change
                        </a>

                        <a class="btn btn-info" @click="openMedia('plugin_accio_seo_facebook_image_'+activeLang, 'image')">Add image</a>

                    </div>
                </div>

            </div>

            <div class="twitter" v-if="activeTab == 'twitter'">
                <!-- twitter title -->
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-2 col-xs-12">Twitter Title: </label>
                    <div class="col-md-10 col-sm-10 col-xs-12">
                        <input type="text" class="form-control" v-model="data.twitterTitle">
                    </div>
                </div>

                <!-- twitter description -->
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-2 col-xs-12">Twitter Description: </label>
                    <div class="col-md-10 col-sm-10 col-xs-12">
                        <input type="text" class="form-control" v-model="data.twitterDescription">
                    </div>
                </div>

                <!-- twitter image -->
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-2 col-xs-12">Facebook Image: </label>
                    <div class="imagePrevContainer col-md-10 col-sm-10 col-xs-12">
                        <div class="imageSingleThumb" v-for="(file, count) in mediaSelectedFiles['plugin_accio_seo_twitter_image_'+activeLang]" :key="count">
                            <i class="fa fa-close closeBtnForPrevImages" @click="removeImage('plugin_accio_seo_twitter_image_')"></i>
                            <img :src="constructMediaUrl(file)">
                        </div>

                        <div class="clearfix"></div>

                        <a class="btn btn-info" @click="openMedia('plugin_accio_seo_twitter_image_'+activeLang, 'image')" v-if="mediaSelectedFiles['plugin_accio_seo_twitter_image_'+activeLang]">
                            Change
                        </a>

                        <a class="btn btn-info" @click="openMedia('plugin_accio_seo_twitter_image_'+activeLang, 'image')">Add image</a>

                    </div>
                </div>


            </div>

        </div>


    </div>
</template>
<style scoped>
    .navigation-seo-social-tabs{
        margin: 0;
        padding: 0;
    }
    .navigation-seo-social-tabs li{
        width: 40px;
        height: 40px;
        background-color: #b5b5b521;
        list-style: none;
        margin: 0;
        padding: 0;
        text-align: center;
        border: 1px solid #EAEAEA;
        cursor: pointer;
        display: inline-block;
    }
    .navigation-seo-social-tabs li i{
        margin-top: 12px;
        font-size: 18px;
    }
    .activeLink{
        background-color: #FFF !important;
    }
</style>
<script>
    import { globalMethods } from '../../../../../../vendor/acciocms/core/src/resources/views/mixins/globalMethods';
    import { globalComputed } from '../../../../../../vendor/acciocms/core/src/resources/views/mixins/globalComputed';
    export default{
        props:['data','activeLang'],
        mixins: [globalComputed, globalMethods],
        data(){
            return{
                activeTab: 'facebook'
            }
        },
        methods:{
            openMedia(inputName, formatType){
                this.$store.commit('setOpenMediaOptions', { multiple: false, has_multile_files: false, multipleInputs: false, format : formatType, inputName: inputName, langSlug: '', clear: false });
                this.$store.commit('setIsMediaOpen', true);
            },
            removeImage(key){
                this.$store.commit('removeSpecificMediaKey', key+this.activeLang);
                if(key === "plugin_accio_seo_facebook_image_"){
                    this.data['facebookMediaID'] = "";
                }

                if(key === "plugin_accio_seo_twitter_image_"){
                    this.data['twitterMediaID'] = "";
                }
            }
        },
        computed:{
            mediaSelectedFiles(){
                // return when user chose files form media
                return this.$store.getters.get_media_selected_files;
            },
            isMediaOpen(){
                // return if media popup is open (true or false)
                return this.$store.getters.get_is_media_open;
            },
        },
        watch:{
            // watch media changes
            isMediaOpen(){
                if(this.mediaSelectedFiles['plugin_accio_seo_facebook_image_'+this.activeLang] !== undefined){
                    this.data['facebookMediaID'] = this.mediaSelectedFiles['plugin_accio_seo_facebook_image_'+this.activeLang][0].mediaID;
                }

                if(this.mediaSelectedFiles['plugin_accio_seo_twitter_image_'+this.activeLang] !== undefined){
                    this.data['twitterMediaID'] = this.mediaSelectedFiles['plugin_accio_seo_twitter_image_'+this.activeLang][0].mediaID;
                }
            }
        }
    }
</script>
