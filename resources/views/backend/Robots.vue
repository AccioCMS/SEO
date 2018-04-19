<template>
    <div>
        <h4>ROBOTS</h4>
        <form class="form-horizontal form-label-left" id="store">

            <!-- robots.txt content -->
            <div class="form-group">
                <label class="control-label col-md-2 col-sm-2 col-xs-12">Edit the content of your robots.txt : </label>
                <div class="col-md-8 col-sm-8 col-xs-12">
                    <textarea class="form-control" rows="20" v-model="content"></textarea>
                </div>
            </div>

        </form>
    </div>
</template>
<script>
    export default{
        computed: {
            content: {
                get() {
                    return this.$store.state.data.robots.content;
                },
                set(value) {
                    this.$store.commit('setData', {group: 'robots', state: 'content', value: value});
                }
            }
        }
    }
</script>
