<template>
  <NcAppContent>
    <div>
      <div
        style="--swiper-navigation-color: #fff; --swiper-pagination-color: #fff"
        class="swiper mySwiper2"
      >
        <div class="swiper-wrapper">
          <div class="swiper-slide">
            <img :src="actualImg" class="image-show" />
          </div>
        </div>
        <div
          class="swiper-button-prev"
          @click="() => changeSlide(selectedKey - 1, selectedKey - 1, 'prev')"
        >
          <a href="#" class="previous round">&#8249;</a>
        </div>
        <div
          class="swiper-button-next"
          @click="() => changeSlide(selectedKey + 1, selectedKey + 1, 'next')"
        >
          <a href="#" class="next round">&#8250;</a>
        </div>
      </div>
      <div thumbsSlider="" class="swiper mySwiper">
        <div class="swiper-wrapper">
          <div
            v-for="(item, index) in images"
            :class="[
              'swiper-slide',
              { 'swiper-slide-thumb-active': item.key === selectedKey },
            ]"
            :id="item.key"
            :key="item.key"
            @click="() => changeSlide(index, item.key, item.fileId)"
          >
            <img :src="item.path" :alt="item.key" />
          </div>
        </div>
      </div>
    </div>
  </NcAppContent>
</template>

<script>
import NcAppContent from "@nextcloud/vue/dist/Components/NcAppContent";
import { BCarouselSlide, BCarousel, BCard } from "bootstrap-vue";
import { NcModal, NcButton, NcTextField } from "@nextcloud/vue";
import { generateRemoteUrl } from "@nextcloud/router";
import { getCurrentUser } from "@nextcloud/auth";
import "swiper/css/thumbs";
import "swiper/css/navigation";
import "swiper/css/free-mode";
import "swiper/css";
import "@nextcloud/dialogs/styles/toast.scss";
import "./style.css";
import { Swiper } from "swiper";
import axios from "axios";

export default {
  name: "App",
  components: {
    NcAppContent,

    NcModal,
    NcButton,
    NcTextField,
    BCarouselSlide,
    BCarousel,
    BCard,
  },
  data() {
    return {
      images: [],
      updating: false,
      loading: true,
      visible: false,
      swiper: null,
      swiper2: null,
      styleSlide: null,
      selectedKey: null,
      actualImg: "",
    };
  },

  methods: {
    sortImg(images) {
      images.sort((a, b) => {
        return new Date(b.dateTaken) - new Date(a.dateTaken);
      });
    },
    async getAllImages() {
      await axios
        .get("/apps/thirdapp/file/index")
        .then((response) => {
          const urls = response.data.map((item, index) => {
            return {
              path: item.thumbnailUrl,
              key: index,
              fileId: item?.fileId,
              dateTaken: item.exif.DateTimeOriginal
                ? item.exif.DateTimeOriginal
                : "",
            };
          });
          this.sortImg(urls);
          this.getActualImg(urls[0].fileId);
          this.images = [...urls];
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {});
      return;
    },

    async getActualImg(fileId) {
      await axios
        .get("/apps/thirdapp/file/getImageByFileId", {
          params: {
            fileId: fileId,
          },
        })
        .then((response) => {
          const filePath = response.data[1].replace(response.data[0], "");
          this.actualImg = `${generateRemoteUrl("dav")}/files/${
            getCurrentUser()?.uid
          }/${filePath}`;
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {});
    },

    changeSlide(index, key, fileId) {
      if (isNaN(fileId)) {
        this.images.find((item) => {
          if (item.key === key) {
            fileId === "next"
              ? (this.selectedKey += 1)
              : (this.selectedKey -= 1);
            this.swiper.slideTo(key);
            this.getActualImg(item.fileId);
          }
        });
      } else {
        this.getActualImg(fileId);
        this.selectedKey = key;
      }
      // this.swiper2.slideTo(index);
    },
    updateSwipers() {
      this.swiper2.update();
      this.swiper.update();
    },
  },

  async mounted() {
    this.loading = false;
    this.getAllImages().then(() => {
      this.$nextTick(() => {
        console.log(document.querySelectorAll(".swiper-slide"));
        this.updateSwipers();
      });
    });

    this.swiper = new Swiper(".mySwiper", {
      loop: true,
      spaceBetween: 10,
      slidesPerView: 4,
      freeMode: true,
      watchSlidesProgress: true,
      pagination: {
        el: ".swiper-pagination",
      },
      mousewheel: true,
      keyboard: true,
    });

    this.swiper2 = new Swiper(".mySwiper2", {
      loop: true,
      spaceBetween: 10,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
    });

    console.log(this.swiper, this.swiper2);
  },
};
</script>
<style scoped>
html,
body {
  position: relative;
  height: 100%;
}

body {
  background: #eee;
  font-family: Helvetica Neue, Helvetica, Arial, sans-serif;
  font-size: 14px;
  color: #000;
  margin: 0;
  padding: 0;
}

.swiper {
  width: 100%;
  height: 100%;
}

.swiper-slide {
  text-align: center;
  font-size: 18px;
  background: #fff;
  display: flex;
  justify-content: center;
  align-items: center;
}

.swiper-slide img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

body {
  background: #000;
  color: #000;
}

.swiper {
  width: 100%;
  margin-left: auto;
  margin-right: auto;
}

.swiper-slide {
  background-size: cover;
  background-position: center;
}

.mySwiper2 {
  width: 100%;
  height: calc(100vh - 200px);
}

.mySwiper {
  box-sizing: border-box;
  padding: 10px 0;
  height: 200px;
}

.mySwiper .swiper-slide {
  width: 25%;
  height: 100%;
  opacity: 0.4;
}

.mySwiper .swiper-slide-thumb-active {
  opacity: 1;
}

.swiper-slide img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
}
a {
  text-decoration: none;
  display: inline-block;
  padding: 8px 16px;
}

a:hover {
  background-color: #ddd;
  color: black;
}

.previous {
  background-color: #f1f1f1;
  color: black;
}

.next {
  background-color: #f1f1f1;
  color: black;
}

.round {
  border-radius: 50%;
}
</style>
