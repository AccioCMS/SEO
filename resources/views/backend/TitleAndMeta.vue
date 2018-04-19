<template>
    <div class="col-md-12 col-xs-12 colons">
        <div class="x_panel">
            <div class="x_content">
                <div class="tabWrapper"><!-- TAB WRAPPER -->
                    <!-- TABS -->
                    <ul class="nav nav-tabs bar_tabs">
                        <li :class="{active: $route.name == 'post-types-meta'}"><router-link :to="{name: 'post-types-meta'}" tag="a">Post Types</router-link></li>
                        <li :class="{active: $route.name == 'category-meta'}"><router-link :to="{name: 'category-meta'}" tag="a">Category</router-link></li>
                        <li :class="{active: $route.name == 'tag-meta'}"><router-link :to="{name: 'tag-meta'}" tag="a">Tag</router-link></li>
                        <li :class="{active: $route.name == 'author-meta'}"><router-link :to="{name: 'author-meta'}" tag="a">Author</router-link></li>
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

    }
</script>
