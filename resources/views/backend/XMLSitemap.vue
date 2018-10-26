<template>
    <div class="col-md-12 col-xs-12 colons">

        <div class="x_panel">
            <div class="x_content">
                <div class="tabWrapper"><!-- TAB WRAPPER -->
                    <!-- TABS -->
                    <ul class="nav nav-tabs bar_tabs">
                        <li :class="{active: $route.name == 'general'}"><router-link :to="{name: 'general'}" tag="a">General</router-link></li>
                        <li :class="{active: $route.name == 'author'}"><router-link :to="{name: 'author'}" tag="a">Author</router-link></li>
                        <li :class="{active: $route.name == 'posts'}"><router-link :to="{name: 'posts'}" tag="a">Posts</router-link></li>
                        <li :class="{active: $route.name == 'categories'}"><router-link :to="{name: 'categories'}" tag="a">Categories</router-link></li>
                        <li :class="{active: $route.name == 'tags'}"><router-link :to="{name: 'tags'}" tag="a">Tags</router-link></li>
                    </ul>

                    <!-- TAB CONTENT -->
                    <div class="tabBody">
                        <router-view></router-view>
                    </div>

                </div>
            </div>
        </div>

    </div>
</template>
<script>
    export default{
        data(){
            return{}
        }
    }
</script>
