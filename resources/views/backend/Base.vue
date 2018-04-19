<template>
    <div class="right_col" role="main">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">

                <div class="x_title">
                    <h2>SEO plugin</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">

                    <div class="col-xs-3">
                        <ul class="nav nav-tabs tabs-left">
                            <li :class="{active: $route.name == 'post-types-meta'
                                            || $route.name == 'category-meta'
                                            || $route.name == 'tag-meta'
                                            || $route.name == 'author-meta'}">
                                <router-link :to="{name: 'post-types-meta'}" tag="a">Title & Meta</router-link>
                            </li>

                            <li :class="{active: $route.name == 'general'
                                            || $route.name == 'posts'
                                            || $route.name == 'tags'
                                            || $route.name == 'categories'
                                            || $route.name == 'author'}">
                                <router-link :to="{name: 'general'}" tag="a">XML Sitemap</router-link>
                            </li>
                            <li :class="{active: $route.name == 'robots'}"><router-link :to="{name: 'robots'}" tag="a">Robots</router-link></li>
                            <!--<li :class="{active: $route.name == 'google-news'}"><router-link :to="{name: 'google-news'}" tag="a">Google News</router-link></li>-->
                            <li :class="{active: $route.name == 'redirect-manager'}"><router-link :to="{name: 'redirect-manager'}" tag="a">Redirect Manager</router-link></li>
                            <!--<li :class="{active: $route.name == 'internationalization'}"><router-link :to="{name: 'internationalization'}" tag="a">Internationalization</router-link></li>-->
                        </ul>
                    </div>

                    <!-- Loading component -->
                    <spinner v-if="spinner" :width="'40px'" :height="'40px'" :border="'10px'"></spinner>

                    <div class="col-xs-9" v-if="!spinner">
                        <router-view></router-view>
                    </div>

                    <div class="clearfix"></div>

                </div>
            </div>
        </div>

        <div class="mainButtonsContainer">
            <div class="row">
                <button type="button" class="btn btn-primary" @click="store" id="globalSaveBtn">Save</button>
            </div>
        </div>

    </div>
</template>
<style scoped>
    a{
        cursor: pointer;
    }
    .mainButtonsContainer button{
        float: right;
        margin-left: 10px;
        margin-top: 10px;
    }
</style>
<script>
    export default{
        data(){
            return{
                postTypesList: [],
                spinner: true,
            }
        },

        created(){
            // get seo settings data from
            const allDataPromise = this.$http.get(this.basePath+'/'+this.$route.params.adminPrefix+'/'+this.$route.params.lang+'/plugins/accio/seo/get-all')
                .then((resp) => {
                    if(Object.keys(resp.body).length){
                        this.$store.state.data = resp.body;
                    }
                }, error => {
                    this.noty("error", error);
                });

            // get post types
            const postTypesPromise = this.$http.get(this.basePath+'/'+this.$route.params.adminPrefix+'/'+this.$route.params.lang+'/json/post-type/get-all')
                .then((resp) => {
                    this.postTypesList = resp.body.data;
                    for (let k in this.postTypesList){
                        if(this.$store.state.data[this.postTypesList[k].slug] === undefined){
                            this.$store.commit('addGroup', {group: this.postTypesList[k].slug, value: { title: '', description: '', robots: '' }});
                        }
                    }
                    this.$store.commit('setPostTypeList', this.postTypesList);
                });

            // when all ajax request are done
            Promise.all([allDataPromise, postTypesPromise]).then(([p1,p2]) => {
                this.spinner = false;
            });
        },

        methods:{
            noty(type, message){
                // noty notification
                new Noty({
                    type: type,
                    layout: 'bottomLeft',
                    text: message,
                    timeout: 3000,
                    closeWith: ['button']
                }).show();
            },
            store(){
                this.$http.post(this.basePath+'/'+this.$route.params.adminPrefix+'/'+this.$route.params.lang+'/plugins/accio/seo/store', this.$store.state.data)
                    .then((resp) => {
                        var response = resp.body;
                        // if there are no errors
                        if(response.code == 200){
                            this.noty("success", response.message);
                        }else{
                        // if there were errors
                            this.noty("error", response.message);
                        }

                    }, error => {
                        this.noty("error", error);
                    });
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