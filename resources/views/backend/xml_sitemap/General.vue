<template>
    <form class="form-horizontal form-label-left" id="store">
        <h4>XML SITEMAP</h4>

        <!-- sitemap is active -->
        <div class="form-group">
            <label class="control-label col-md-2 col-sm-2 col-xs-12">XML sitemap functionality: </label>
            <div class="col-md-8 col-sm-8 col-xs-12">

                <div id="sitemapIsActive" class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default yes" :class="{active: $store.state.data.sitemap.isActive == true}" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default" @click="setSitemapActive(true)">
                        <input type="radio" value="enabled"> &nbsp; Enabled &nbsp;
                    </label>
                    <label class="btn btn-primary no" :class="{active: $store.state.data.sitemap.isActive == false}" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default" @click="setSitemapActive(false)">
                        <input type="radio" value="disabled"> Disabled
                    </label>
                </div>

            </div>
        </div>

        <!-- sitemap entries -->
        <div class="form-group">
            <label class="control-label col-md-2 col-sm-2 col-xs-12">Max entries per sitemap: </label>
            <div class="col-md-8 col-sm-8 col-xs-12">
                <input type="text" class="form-control" v-model="maxEntriesAllowed">
            </div>
        </div>

    </form>
</template>
<script>
    export default{
        methods:{
            setSitemapActive(value){
                this.$store.commit('setData', {group: 'sitemap', state: 'isActive', value: value});
            }
        },
        computed: {
            maxEntriesAllowed: {
                get() {
                    return this.$store.state.data.sitemap.maxEntriesAllowed;
                },
                set(value) {
                    this.$store.commit('setData', {group: 'sitemap', state: 'maxEntriesAllowed', value: value});
                }
            }
        }
    }
</script>
