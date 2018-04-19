<template>
    <form class="form-horizontal form-label-left" id="store">
        <h4>GENERAL</h4>
        <!-- title -->
        <div class="form-group">
            <label class="control-label col-md-2 col-sm-2 col-xs-12">SEO title: </label>
            <div class="col-md-10 col-sm-10 col-xs-12">
                <input type="text" class="form-control" v-model="data.title">
            </div>
        </div>

        <!-- Meta description -->
        <div class="form-group">
            <label class="control-label col-md-2 col-sm-2 col-xs-12">Meta Description: </label>
            <div class="col-md-10 col-sm-10 col-xs-12">
                <textarea class="form-control" v-model="data.description"></textarea>
            </div>
        </div>

    </form>
</template>
<script>
    export default{
        props:['data'],
    }
</script>
