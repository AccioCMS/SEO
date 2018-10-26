<template>
    <form class="form-horizontal form-label-left" id="store">

        <!-- POST TYPES -->
        <template v-for="postType in get_post_types">
            <h4 style="text-transform: uppercase;">{{ postType.name }}</h4>
            <div class="form-group">
                <label class="control-label col-md-2 col-sm-2 col-xs-12">Title: </label>
                <div class="col-md-8 col-sm-8 col-xs-12">
                    <input type="text" class="form-control" @change="dataChanged($event, postType.slug, 'title')" :value="getVModelData(postType.slug, 'title')">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-md-2 col-sm-2 col-xs-12">Meta Description: </label>
                <div class="col-md-8 col-sm-8 col-xs-12">
                    <textarea class="form-control" @change="dataChanged($event, postType.slug, 'description')" :value="getVModelData(postType.slug, 'description')"></textarea>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-md-2 col-sm-2 col-xs-12">Meta Robots: </label>
                <div class="col-md-8 col-sm-8 col-xs-12">

                    <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-default yes" :class="{active: getVModelData(postType.slug, 'robots') == true}" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default" @click="changeBoolean(postType.slug, 'robots', true)">
                            <input type="radio" value="enabled"> &nbsp; Enabled &nbsp;
                        </label>
                        <label class="btn btn-primary no" :class="{active: getVModelData(postType.slug, 'robots') == false}" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default" @click="changeBoolean(postType.slug, 'robots', false)">
                            <input type="radio" value="disabled"> Disabled
                        </label>
                    </div>

                </div>
            </div>
            <hr>
        </template>

        <div class="alert alert-success">
            <h5>Variables to use within fields</h5>
            <p>{{ titlePlaceholder }} - Will be replaced with the title of the post/page</p>
            <p>{{ sitenamePlaceholder }} - The site's name</p>
            <p>{{ pagePlaceholder }} - Will be replaced with the current page number (i.e. page 2 of 4)</p>
        </div>

    </form>
</template>

<script>
    export default {
        data(){
            return{
                titlePlaceholder: '{{title}}',
                sitenamePlaceholder: '{{sitename}}',
                pagePlaceholder: '{{page}}',

            }
        },
        methods:{
            dataChanged(event, postType, type){
                this.$store.commit('setPostTypeData', {postType: postType, type: type, value: event.target.value});
            },
            getVModelData(postType, type){
                return this.$store.state.data[postType][type];
            },
            changeBoolean(postType, key, value){
                this.$store.commit('setData', {group: postType, state: key, value: value});
            }
        },
        computed: {
            // get base path
            basePath(){
                return this.$store.getters.get_base_path;
            },
            get_post_types(){
                return this.$store.getters.get_post_types;
            }
        }
    }
</script>