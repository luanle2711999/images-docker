import VueRouter from "vue-router";
import HomeView from "../views/HomeView.vue";
import ImagesView from "../views/ImagesView.vue";
import ListFolder from "../views/ListingFolder/ListFolder.vue";
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
  //   component: ImagesView,
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
