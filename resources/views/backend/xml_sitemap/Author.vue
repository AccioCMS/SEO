<template>
    <form class="form-horizontal form-label-left" id="store">
        <h4>AUTHOR SITEMAP</h4>

        <!-- sitemap is active -->
        <div class="form-group">
            <label class="control-label col-md-2 col-sm-2 col-xs-12">Author sitemap : </label>
            <div class="col-md-8 col-sm-8 col-xs-12">

                <div id="sitemapIsActive" class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default yes" :class="{active: $store.state.data.users.authorSitemap == true}" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default" @click="changeBoolean(true)">
                        <input type="radio" value="enabled"> &nbsp; Enabled &nbsp;
                    </label>
                    <label class="btn btn-primary no" :class="{active: $store.state.data.users.authorSitemap == false}" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default" @click="changeBoolean(false)">
                        <input type="radio" value="disabled"> Disabled
                    </label>
                </div>

            </div>
        </div>
    </form>
</template>
<style scoped>
    h4{
        margin-bottom: 30px;
    }
</style>
<script>
    export default{
        methods:{
            changeBoolean(value){
                this.$store.commit('setData', {group: 'users', state: 'authorSitemap', value: value});
            }
        },
    }
</script>
