<template>
    <div id="top_carouse" class="carousel carousel-dark slide" data-bs-ride="carousel"
         :data-bs-interval="carouselSettings.interval" v-if="items.length">
        <div class="carousel-indicators" v-if="showIndicators">
            <button type="button" data-bs-target="#top_carouse" :data-bs-slide-to="index" class="active"
                    v-for="(item, index) in items"></button>
        </div>
        <div class="carousel-inner">
            <div :class="{'carousel-item': true, 'active': index === 0}" v-for="(item, index) in items" @click="toLink(item)">
                <picture>
                    <source :srcset="linkToImg(srcset)" :media="`(max-width: ${mediaWidth(srcset)}px)`" v-for="srcset in item.srcset">
                    <img class="d-block lazy-img"
                         v-lazy.container="item.slide"
                         :alt="item.title"
                         lazy="loading">
                </picture>
                <div class="carousel-caption d-none d-md-block" v-if="item.description" v-html="item.description"></div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#top_carouse" data-bs-slide="prev"
                v-if="carouselSettings.nav && items.length > 1">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#top_carouse" data-bs-slide="next"
                v-if="carouselSettings.nav && items.length > 1">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</template>

<script>
import axios from "axios";
import {mapGetters} from "vuex";

export default {
    name: "SliderTop",
    props: {
        pageId: {
            type: Number,
            required: true
        },
        pageType: {
            type: String,
            default: 'page'
        },
        position: {
            type: String,
            default: 'top'
        }
    },
    computed: {
        showIndicators() {
            return this.items.length > 1 && this.carouselSettings.indicators
        },
        source() {
            return '/get-slides'
        }
    },
    data() {
        return {
            items: [],
            carouselSettings: []
        }
    },
    async fetch() {
        let data = {
            params: {
                page_id: this.pageId,
                type: this.pageType,
                position: this.position
            }
        };
        await axios.get(this.source, data).then(response => {
            this.items = response.data.data;
            if (typeof response.data.carousel_settings !== "undefined") {
                this.carouselSettings = response.data.carousel_settings
            }
        }).catch(() => {
        });
    },

    methods: {
        toLink(item) {
            if (item.link)
                window.open(item.link, item.target)
        },
        showSrcset(item) {
            let srcset = item.srcset;
            if (!process.server && item.mobile_srcset.length) {
                let mobileSizes = [];
                item.mobile_srcset.forEach(item => {
                    let itemSplit = item.split(' ')
                    if (itemSplit.length > 1) {
                        mobileSizes.push(parseInt(itemSplit[1]))
                    }
                })

                mobileSizes.sort(function (a, b) {
                    return a - b;
                });

                if (window.innerWidth <= mobileSizes[mobileSizes.length - 1]) {
                    srcset = item.mobile_srcset
                }
            }

            return srcset
        },
        putSrc(src) {
            return process.client ? src : null
        },
        linkToImg(item) {
            return item.split(' ')[0]
        },
        mediaWidth(item) {
            return item.split(' ')[1].replace('w', '')
        }
    }
}
</script>