<template>
    <ul class="nav nav-tabs bar_tabs" v-if="Object.keys(getLanguages).length > 1">
        <li role="presentation" class="langTabs" :class="{active: activeLang == lang.slug}"
            :id="'tabBtn-'+lang.slug"
            v-for="(lang, count) in getLanguages"
            :key="count"
            :data-lang="lang.slug"
            @click="$emit('activeLangChanged', lang.slug)">

            <a :href="'#tab_content'+ ++count" :id="'lang-tab'+count">{{ lang.name }}</a>

        </li>
    </ul>

</template>

<script>
    export default {
        props:["activeLang"],
        computed: {
            getLanguages(){
                return this.$store.getters.get_local_languages;
            }
        }
    }
</script>