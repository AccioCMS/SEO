require('../../../../../../resources/assets/js/bootstrap');
require('../../../../../../vendor/acciocms/core/src/resources/assets/js/base-components');
require('vue-multiselect/dist/vue-multiselect.min.css');

import Vue from 'vue'
import VueRouter from 'vue-router';
import { store } from './store';
import Base from '../../views/backend/Base.vue';
import TitleAndMeta from '../../views/backend/TitleAndMeta.vue';
import Robots from '../../views/backend/Robots.vue';
import GoogleNews from '../../views/backend/GoogleNews.vue';
import RedirectManager from '../../views/backend/RedirectManager.vue';
import Internationalization from '../../views/backend/Internationalization.vue';
import XMLSitemap from '../../views/backend/XMLSitemap.vue';
import General from '../../views/backend/xml_sitemap/General.vue';
import Author from '../../views/backend/xml_sitemap/Author.vue';
import Posts from '../../views/backend/xml_sitemap/Posts.vue';
import Categories from '../../views/backend/xml_sitemap/Categories.vue';
import Tags from '../../views/backend/xml_sitemap/Tags.vue';

import PostTypesMeta from '../../views/backend/title_and_meta/PostTypes.vue';
import CategoryMeta from '../../views/backend/title_and_meta/Category.vue';
import TagMeta from '../../views/backend/title_and_meta/Tag.vue';
import AuthorMeta from '../../views/backend/title_and_meta/Author.vue';
import MenuLinks from '../../views/backend/title_and_meta/MenuLinks.vue';

Vue.use(VueRouter);

const routes = [
    { path: globalProjectDirectory+'/:adminPrefix/:lang/plugins/accio/seo', component: Base, children: [
        { path: '', component: TitleAndMeta, children: [
                { path: '', component: PostTypesMeta, name: 'post-types-meta' },
                { path: 'category-meta', component: CategoryMeta, name: 'category-meta' },
                { path: 'tag-meta', component: TagMeta, name: 'tag-meta' },
                { path: 'author-meta', component: AuthorMeta, name: 'author-meta' },
                { path: 'menu-link', component: MenuLinks, name: 'menu-links-meta' },
            ]},
        { path: 'xml-sitemap', component: XMLSitemap, children: [
                { path: '', component: General, name: 'general' },
                { path: 'author', component: Author, name: 'author' },
                { path: 'posts', component: Posts, name: 'posts' },
                { path: 'categories', component: Categories, name: 'categories' },
                { path: 'tags', component: Tags, name: 'tags' },
            ]},
        { path: 'robots', component: Robots, name: 'robots' },
        { path: 'google-news', component: GoogleNews, name: 'google-news' },
        { path: 'redirect-manager', component: RedirectManager, name: 'redirect-manager' },
        { path: 'internationalization', component: Internationalization, name: 'internationalization' },
    ]},
];

const router = new VueRouter({
    mode: 'history',
    routes: routes
});

const app = new Vue({
    el: '#app',
    router,
    store,
});