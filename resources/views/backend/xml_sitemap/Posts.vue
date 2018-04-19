<template>
    <div>
        <!-- Loading component -->
        <spinner v-if="spinner" :width="'40px'" :height="'40px'" :border="'10px'"></spinner>

        <form class="form-horizontal form-label-left" id="store" v-if="!spinner">

            <h4>Post of Post Types</h4>
            <!-- sitemap for post types-->

            <div class="form-group">
                <div class="col-md-2 col-sm-2 col-xs-12"></div>
                <div class="col-md-10 col-sm-10 col-xs-12">
                    <div class="inputContainer"><input type="checkbox" @change="checkAllPostTypes" v-model="selectAll" id="selectAll"></div>
                    <div class="labelContainer"><label for="selectAll">Select all</label></div>
                </div>
            </div>

            <div class="postTypeContainer" v-for="postType in postTypesList">

                <div class="form-group">
                    <div class="col-md-2 col-sm-2 col-xs-12"></div>
                    <div class="col-md-10 col-sm-10 col-xs-12">
                        <div class="inputContainer"><input type="checkbox" :value="postType.postTypeID" v-model="postTypesInSitemap" :id="postType.slug"></div>
                        <div class="labelContainer"><label :for="postType.slug">{{ postType.name }}</label></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-2 col-xs-12">Exclude posts of {{ postType.name }}: </label>
                    <div class="col-md-8 col-sm-8 col-xs-12">
                        <multiselect
                                v-model="postsIgnoredInSitemap[postType.slug]"
                                :options="postOptions"
                                :multiple="true"
                                :close-on-select="true"
                                :clear-on-select="false"
                                :hide-selected="true"
                                placeholder="Search with title"
                                label="title"
                                track-by="postID"
                                :searchable="true"
                                :loading="isLoading[postType.slug]"
                                :disabled="isDisabled(postType.postTypeID)"
                                @search-change="searchPost($event, postType.slug)"></multiselect>
                    </div>
                </div>

            </div>

        </form>

    </div>
</template>
<style scoped>
    .postTypeContainer{
        padding-top: 30px;
        padding-bottom: 30px;
        margin-top: 30px;
        margin-bottom: 30px;
        border: 1px solid #EAEAEA;
    }
    input[type="checkbox"]{
        width: 18px;
        height: 18px;
    }
    .inputContainer, .labelContainer{
        float: left;
    }
    .labelContainer label{
        margin-top: 5px;
        margin-left: 5px;
    }
    h4{
        margin-bottom: 30px;
        padding-bottom: 10px;
        border-bottom: 1px solid #EAEAEA;
    }
</style>
<script>
    export default{
        created(){
            this.$http.get(this.basePath+'/'+this.$route.params.adminPrefix+'/'+this.$route.params.lang+'/json/post-type/get-all')
                .then((resp) => {
                    this.postTypesList = resp.body.list;
                    for(let k in this.postTypesList){
                        this.isLoading[this.postTypesList[k].slug] = false;
                    }
                }).then((resp) => {
                    this.spinner = false;
                });
        },
        data(){
            return{
                postTypesList: [],
                postOptions: [],
                selectAll: false,
                isLoading: {},
                spinner: false,
            }
        },
        methods:{
            checkAllPostTypes(){
                let tmp = [];
                if(this.selectAll == true){
                    for(let k in this.postTypesList){
                        tmp.push(this.postTypesList[k].postTypeID);
                    }
                }
                this.$store.commit('setData', {state: 'postTypesInSitemap', value: tmp});
            },

            searchPost(query, postTypeSlug){
                this.postOptions = [];
                if(!this.isLoading[postTypeSlug] && query.length > 0){
                    this.isLoading[postTypeSlug] = true;
                    this.$http.get(this.basePath+'/'+this.$route.params.adminPrefix+'/'+this.$route.params.lang+'/json/posts/search/'+postTypeSlug+'/'+query)
                        .then((resp) => {
                            this.postOptions = resp.body.list;
                            this.isLoading[postTypeSlug] = false;
                        }, error => {
                            console.log(error);
                        });
                }
            },
            isDisabled(postID){
                if(this.postTypesInSitemap.indexOf(postID) != -1){
                    return false;
                }
                return true;
            }
        },
        computed:{
            // get base path
            basePath(){
                return this.$store.getters.get_base_path;
            },
            postTypesInSitemap: {
                get() {
                    return this.$store.state.data.post.postTypesInSitemap;
                },
                set(value) {
                    this.$store.commit('setData', {group: 'post', state: 'postTypesInSitemap', value: value});
                }
            },
            postsIgnoredInSitemap: {
                get() {
                    return this.$store.state.data.post.postsIgnoredInSitemap;
                },
                set(value) {
                    this.$store.commit('setData', {group: 'post', state: 'postsIgnoredInSitemap', value: value});
                }
            }
        }
    }
</script>
