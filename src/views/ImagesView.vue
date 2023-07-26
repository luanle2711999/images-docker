<template>
  <div id="content" class="app-snextcloud">
    <NcAppNavigation>
      <button @click="$emit('goBackward')" class="btn-back">Back</button>
      <FolderTreeList
        :dataSource="dataSoureSidebar"
        test="dsfksdhfkjds"
        v-on:queryImgesAfterFetch="queryImgesAfterFetch"
      />
    </NcAppNavigation>
    <NcAppContent>
      <div>
        <div
          style="
            --swiper-navigation-color: #fff;
            --swiper-pagination-color: #fff;
          "
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
  </div>
</template>

<script>
import NcAppContent from "@nextcloud/vue/dist/Components/NcAppContent";
import NcAppNavigation from "@nextcloud/vue/dist/Components/NcAppNavigation";
import { BCarouselSlide, BCarousel, BCard } from "bootstrap-vue";
import { NcModal, NcButton, NcTextField } from "@nextcloud/vue";
import { generateRemoteUrl } from "@nextcloud/router";
import FolderTreeList from "./ViewTreeList/FolderTreeList.vue";
import { getCurrentUser } from "@nextcloud/auth";
import { VueTreeList, Tree, TreeNode } from "vue-tree-list";
import { groupBy } from "lodash";
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
    NcAppNavigation,
    NcModal,
    NcButton,
    NcTextField,
    BCarouselSlide,
    BCarousel,
    BCard,
    FolderTreeList,
  },
  data() {
    return {
      dataSoureSidebar: [],
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
  props: ["fileId", "folderName"],
  methods: {
    sortImg(images) {
      images.sort((a, b) => {
        return new Date(b.dateTaken) - new Date(a.dateTaken);
      });
    },
    async getAllImages() {
      await axios
        .get("/apps/thirdapp/file/index", {
          params: {
            fileId: this.fileId,
          },
        })
        .then(async (response) => {
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
          const dataSourceSidebar = this.getDataSidebar(response.data);
          this.dataSoureSidebar = [...dataSourceSidebar];
          this.sortImg(urls);
          await this.getActualImg(urls[0].fileId);
          this.images = [...urls];
          // this.swiper.slideTo(urls[0].key);
          // this.swiper2.slideTo(urls[0].key);
          // this.updateSwipers();
          localStorage.setItem("allImages", JSON.stringify(this.images));
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {});
      return;
    },

    getYearTaken(date) {
      return new Date(date?.split(" ")[0].split(":").join("-")).getFullYear();
    },
    getMonthTaken(date) {
      return new Date(date?.split(" ")[0].split(":").join("-")).getMonth() + 1;
    },
    async queryImgesAfterFetch(dateTaken, type) {
      const yearTaken = this.getYearTaken(dateTaken);
      const monthTaken = this.getMonthTaken(dateTaken);
      const allImages = JSON.parse(localStorage.getItem("allImages"));
      let imagesAfterFilter = [];
      if (!dateTaken) {
        console.log(allImages);
        imagesAfterFilter = [...allImages];
      } else {
        if (dateTaken !== "undefined") {
          if (type === "year") {
            allImages.map((item) => {
              const year = this.getYearTaken(item?.dateTaken);
              if (year === yearTaken) {
                imagesAfterFilter.push(item);
              }
            });
          } else if (type === "month") {
            allImages.map((item) => {
              const month = this.getMonthTaken(item?.dateTaken);
              const year = this.getYearTaken(item?.dateTaken);
              if (month === monthTaken && year === yearTaken) {
                imagesAfterFilter.push(item);
              }
            });
          } else {
            imagesAfterFilter = [...allImages];
          }
        } else {
          if (type === "year") {
            allImages.map((item) => {
              const year = this.getYearTaken(item?.dateTaken);
              if (isNaN(year) && isNaN(yearTaken)) {
                imagesAfterFilter.push(item);
              }
            });
          } else if (type === "month") {
            allImages.map((item) => {
              const month = this.getMonthTaken(item?.dateTaken);
              const year = this.getYearTaken(item?.dateTaken);
              if (
                isNaN(month) &&
                isNaN(year) &&
                isNaN(monthTaken) &&
                isNaN(yearTaken)
              ) {
                imagesAfterFilter.push(item);
              }
            });
          } else {
            imagesAfterFilter = [...allImages];
          }
        }
      }

      this.images = [...imagesAfterFilter];
      await this.getActualImg(imagesAfterFilter[0].fileId);
      this.updateSwipers();
    },
    convertNumberToMonth(monthNumber) {
      var monthNames = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
      ];
      return monthNames[Number(monthNumber) - 1];
    },
    buildObjForMonthLevel(Obj) {
      const result = [];
      for (const [key, value] of Object.entries(
        groupBy(Obj, (item) => item?.monthTaken)
      )) {
        result.push({
          name: this.convertNumberToMonth(key),
          id: key,
          type: "month",
          dateTaken: value && value.length ? value[0].dateTaken : "undefined",
          pid: 2,
          amount: value && value?.length ? value?.length : 0,
        });
      }
      return result;
    },
    buildObjForYearLevel(Obj) {
      const result = [];
      for (const [key, value] of Object.entries(Obj)) {
        const x = {
          name: key,
          id: key,
          type: "year",
          pid: 1,
          dateTaken: value && value.length ? value[0].dateTaken : "undefined",
          amount: value && value.length ? value.length : 0,
          children: this.buildObjForMonthLevel(value),
        };
        result.push(x);
      }
      return result;
    },
    getDataSidebar(data) {
      const dataSample = data?.map((item) => {
        return {
          thumbnailUrl: item.thumbnailUrl,
          dateTaken: item.exif.DateTimeOriginal
            ? item.exif.DateTimeOriginal
            : "undefined",
          fileId: item.fileId,
          yearTaken: item?.exif?.DateTimeOriginal
            ? new Date(
                item?.exif?.DateTimeOriginal?.split(" ")[0].split(":").join("-")
              ).getFullYear()
            : "undefined",
          monthTaken: item?.exif?.DateTimeOriginal
            ? new Date(
                item?.exif?.DateTimeOriginal?.split(" ")[0].split(":").join("-")
              ).getMonth() + 1
            : "undefined",
        };
      });
      const objInYearLevel = groupBy(dataSample, (item) => item.yearTaken);
      const result = this.buildObjForYearLevel(objInYearLevel);
      const ObjReturn = [
        {
          name: this.folderName,
          amount: dataSample.length,
          type: "all",
          id: 0,
          pid: 0,
          children: result,
        },
      ];
      return ObjReturn;
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
/* .swiper-wrapper {
  display: flex;
  justify-content: center;
} */
.btn-back {
  margin-left: 5px;
}
</style>
