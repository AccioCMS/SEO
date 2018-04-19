<template>
    <div>
        <form class="form-horizontal form-label-left" id="store">

            <h4>Categories of Post Types</h4>
            <!-- sitemap for post types-->

            <div class="form-group">
                <div class="col-md-2 col-sm-2 col-xs-12"></div>
                <div class="col-md-10 col-sm-10 col-xs-12">
                    <div class="inputContainer"><input type="checkbox" @change="checkAllPostTypes" v-model="selectAll" id="selectAll"></div>
                    <div class="labelContainer"><label for="selectAll">Select all</label></div>
                </div>
            </div>

            <div class="postTypeContainer" v-for="postType in postTypesList" v-if="postType.hasCategories">

                <div class="form-group">
                    <div class="col-md-2 col-sm-2 col-xs-12"></div>
                    <div class="col-md-10 col-sm-10 col-xs-12">
                        <div class="inputContainer"><input type="checkbox" :value="postType.postTypeID" v-model="postTypesInSitemap" :id="postType.slug"></div>
                        <div class="labelContainer"><label :for="postType.slug">{{ postType.name }}</label></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-2 col-xs-12">Exclude categories of {{ postType.name }}: </label>
                    <div class="col-md-8 col-sm-8 col-xs-12">
                        <multiselect
                                v-model="categoriesIgnoredInSitemap[postType.slug]"
                                :options="categoryOptions"
                                :multiple="true"
                                :close-on-select="true"
                                :clear-on-select="false"
                                :hide-selected="true"
                                placeholder="Search with title"
                                label="title"
                                track-by="categoryID"
                                :searchable="true"
                                :loading="isLoading[postType.slug]"
                                :disabled="isDisabled(postType.postTypeID)"
                                @search-change="searchCategory($event, postType.slug, postType.postTypeID)"></multiselect>
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
                });
        },
        data(){
            return{
                postTypesList: [],
                categoryOptions: [],
                selectAll: false,
                isLoading: {},
            }
        },
        methods:{
            checkAllPostTypes(){
                let tmp = [];
                if(this.selectAll == true){
                    for(let k in this.postTypesList){
                        if(this.postTypesList[k].hasCategories){
                            tmp.push(this.postTypesList[k].postTypeID);
                        }
                    }
                }
                this.$store.commit('setData', {group: 'categories', state: 'postTypesInSitemap', value: tmp});
            },

            searchCategory(query, postTypeSlug, postTypeID){
                this.postOptions = [];
                if(!this.isLoading[postTypeSlug] && query.length > 0){
                    this.isLoading[postTypeSlug] = true;
                    this.$http.get(this.basePath+'/'+this.$route.params.adminPrefix+'/'+this.$route.params.lang+'/json/category/'+postTypeID+'/search/'+query)
                        .then((resp) => {
                            this.categoryOptions = resp.body.list;
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
                    return this.$store.state.data.categories.postTypesInSitemap;
                },
                set(value) {
                    this.$store.commit('setData', {group: 'categories', state: 'postTypesInSitemap', value: value});
                }
            },
            categoriesIgnoredInSitemap: {
                get() {
                    return this.$store.state.data.categories.categoriesIgnoredInSitemap;
                },
                set(value) {
                    this.$store.commit('setData', {group: 'categories', state: 'categoriesIgnoredInSitemap', value: value});
                }
            }
        }
    }
</script>
