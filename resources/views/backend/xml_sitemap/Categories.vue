<template>
    <div>
        <form class="form-horizontal form-label-left" id="store">

            <h4>Sitemaps for Categories</h4>
            <!-- sitemap is active -->
            <div class="form-group">
                <label class="control-label col-md-2 col-sm-2 col-xs-12">Author sitemap : </label>
                <div class="col-md-8 col-sm-8 col-xs-12">

                    <div id="sitemapIsActive" class="btn-group" data-toggle="buttons">
                        <label class="btn btn-default yes" :class="{active: $store.state.data.categories.categoriesSitemap == true}" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default" @click="changeBoolean(true)">
                            <input type="radio" value="enabled"> &nbsp; Enabled &nbsp;
                        </label>
                        <label class="btn btn-primary no" :class="{active: $store.state.data.categories.categoriesSitemap == false}" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default" @click="changeBoolean(false)">
                            <input type="radio" value="disabled"> Disabled
                        </label>
                    </div>

                </div>
            </div>

        </form>

    </div>
</template>
<style scoped>
    h4{
        margin-bottom: 30px;
        padding-bottom: 10px;
        border-bottom: 1px solid #EAEAEA;
    }
</style>
<script>
    export default{
        methods:{
            changeBoolean(value){
                this.$store.commit('setData', {group: 'categories', state: 'categoriesSitemap', value: value});
            },
        },
        computed:{
            // get data
            getData(){
                return this.$store.getters.get_data;
            }
        }
    }
</script>
