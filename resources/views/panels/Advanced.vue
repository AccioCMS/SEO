<template>
    <div class="col-lg-12 col-md-12">
        <h4>ADVANCED</h4>

        <!-- INDEX -->
        <div class="form-group">
            <label class="control-label col-md-2 col-sm-2 col-xs-12">Meta robots index: </label>
            <div class="col-md-10 col-sm-10 col-xs-12">
                <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default yes" :class="{active: data.isIndex == true}" @click="data.isIndex = true">
                        <input type="radio" value="enabled"> &nbsp; Index &nbsp;
                    </label>
                    <label class="btn btn-primary no" :class="{active: data.isIndex == false}" @click="data.isIndex = false">
                        <input type="radio" value="disabled"> No Index
                    </label>
                </div>
            </div>
        </div>

        <!-- Follow - No Follow -->
        <div class="form-group">
            <label class="control-label col-md-2 col-sm-2 col-xs-12">Meta robots follow: </label>
            <div class="col-md-8 col-sm-8 col-xs-12">

                <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default yes" :class="{active: data.isFollow == true}" @click="data.isFollow = true">
                        <input type="radio" value="enabled"> &nbsp; Follow &nbsp;
                    </label>
                    <label class="btn btn-primary no" :class="{active: data.isFollow == false}" @click="data.isFollow = false">
                        <input type="radio" value="disabled"> No Follow
                    </label>
                </div>

            </div>
        </div>

        <!-- Canonical -->
        <div class="form-group">
            <label class="control-label col-md-2 col-sm-2 col-xs-12">Canonical URL: </label>
            <div class="col-md-10 col-sm-10 col-xs-12">
                <input type="text" class="form-control" v-model="data.canonicalURL">
                <p>Leave empty to default permalink. Cross domain conanical supported too.</p>
            </div>
        </div>

    </div>
</template>
<script>
    export default{
        props: ['data'],
        data(){
            return{}
        }
    }
</script>
