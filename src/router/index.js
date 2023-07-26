import VueRouter from "vue-router";
import HomeView from "../views/HomeView.vue";
import FolderTreeList from "../views/ViewTreeList/FolderTreeList.vue";
import ListFolder from "../views/ListingFolder/ListFolder.vue";
import ImagesViewVue from "../views/ImagesView.vue";
const routes = [
  {
    path: "/",
    name: "Home",
    component: HomeView,
  },

  // lazy load all other route like the following:
  // {
  //   path: "/images",
  //   name: "ComponentName",
  //   component: ImagesViewVue,
  // },
  {
    path: "/folders",
    name: "ListFolder",
    component: ListFolder,
  },
];

export const router = new VueRouter({
  routes,
});
