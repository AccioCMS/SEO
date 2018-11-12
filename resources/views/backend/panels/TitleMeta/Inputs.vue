<template>
    <div>
        <h4 style="text-transform: uppercase;">{{ label }}</h4>

        <div class="form-group">
            <label class="control-label col-md-2 col-sm-2 col-xs-12">Title: </label>
            <div class="col-md-8 col-sm-8 col-xs-12">
                <input type="text" class="form-control" @change="dataChanged($event, slug, 'title')" :value="title">
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-md-2 col-sm-2 col-xs-12">Meta Description: </label>
            <div class="col-md-8 col-sm-8 col-xs-12">
                <textarea class="form-control" @change="dataChanged($event, slug, 'description')" :value="description"></textarea>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-md-2 col-sm-2 col-xs-12">Meta Robots: </label>
            <div class="col-md-8 col-sm-8 col-xs-12">

                <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default yes" :class="{active: robots == true}" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default" @click="changeBoolean(slug, 'robots', true)">
                        <input type="radio" value="enabled">&nbsp Enabled &nbsp;
                    </label>
                    <label class="btn btn-primary no" :class="{active: robots == false}" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default" @click="changeBoolean(slug, 'robots', false)">
                        <input type="radio" value="disabled"> Disabled
                    </label>
                </div>

            </div>
        </div>
        <hr>
    </div>

</template>

<script>
    export default {
        props: {
            label: String,
            title: String,
            slug: String,
            description: String,
            robots: Boolean|String,
            activeLang: String,
        },

        methods:{
            dataChanged(event, key, type){
                this.$store.commit('setData', {group: key, state: type, value: event.target.value, lang: this.activeLang});
            },
            getVModelData(menuLinkKey, type){
                return this.$store.state.data[menuLinkKey][type];
            },
            changeBoolean(group, key, value){
                this.$store.commit('setData', {group: group, state: key, value: value, lang: this.activeLang});
            }
        }
    }
</script>