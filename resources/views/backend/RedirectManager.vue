<template>
    <div>
        <h4>REDIRECT MANAGER</h4>
        <form class="form-horizontal form-label-left" id="store">

            <!-- redirect manager content -->
            <div class="form-group">
                <p class="col-md-12 col-sm-12 col-xs-12">One redirect per line. Blank lines and lines that start with # (hash) will be ignored </p>
                <p class="col-md-12 col-sm-12 col-xs-12">/old-link/  http://www.domain.com/new-link/</p>
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
                get(){
                    return this.$store.state.data.redirectManager.content;
                },
                set(value){
                    this.$store.commit('setData', {group: 'redirectManager', state: 'content', value: value});
                }
            }
        }
    }
</script>
