import VueRouter from "vue-router";
import HomeView from "../views/HomeView.vue";
import ImagesView from "../views/ImagesView.vue";
import ListingFolder from "../views/ListingFolder/ListingFolder.vue";
const routes = [
  {
    path: "/home",
    name: "Home",
    component: HomeView,
  },

  // lazy load all other route like the following:
  {
    path: "/images",
    name: "ComponentName",
    component: ImagesView,
  },
  {
    path: "/*",
    name: "ListingFolder",
    component: ListingFolder,
  },
];

export const router = new VueRouter({
  mode: "history",
  routes,
});
