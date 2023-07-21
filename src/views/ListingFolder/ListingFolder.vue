<template>
  <div id="content" class="app-snextcloud">
    <!-- <NcAppNavigation> -->
    <!-- <button class="add-new"  v-b-modal.modal-1>Add new folder</button> -->
    <!-- <b-button v-b-modal.modal-1 class="add-new">Add new folder</b-button>
        <b-modal
          id="modal-1"
          title="Create a new folder"
          @ok="() => createNewFolder(folderName)"
        >
          <div>
            <b-form @submit="() => {}" @reset="() => {}">
              <b-form-group
                id="input-group-1"
                label="Folder Name:"
                label-for="input-1"
              >
                <b-form-input
                  id="input-1"
                  v-model="folderName"
                  placeholder="Enter folder name"
                  required
                ></b-form-input>
              </b-form-group>
            </b-form>
          </div>
        </b-modal> -->
    <!-- </NcAppNavigation> -->
    <NcAppContent>
      <div class="file-container">
        <b-breadcrumb :items="paths"></b-breadcrumb>
        <b-table
          :fields="fields"
          :items="directories"
          class="table table-hover"
          @row-clicked="onRowClicked"
        >
          <template #cell(basename)="data">
            <!-- <div v-if="data.type === 'directory'">
                <b-icon icon="info-circle-fill">{{ data.value }}</b-icon>
              </div> -->
            <div v-if="data.item.type === 'directory'">
              <b-icon icon="folder2"></b-icon>
              <span>{{ data.item.basename }}</span>
            </div>
            <div v-else>
              <b-icon icon="file-earmark"></b-icon>
              <span>{{ data.item.basename }}</span>
            </div>
          </template>
        </b-table>
        <button @click="showImges()" v-if="checkFolderIncludeImg(directories)">
          Live show
        </button>
      </div>
    </NcAppContent>
  </div>
</template>

<script>
import NcAppContent from "@nextcloud/vue/dist/Components/NcAppContent";
import NcAppNavigation from "@nextcloud/vue/dist/Components/NcAppNavigation";
import NcHeaderMenu from "@nextcloud/vue/dist/Components/NcHeaderMenu";
import NcEmptyContent from "@nextcloud/vue/dist/Components/NcEmptyContent";
import { NcModal, NcButton, NcTextField } from "@nextcloud/vue";
import { createClient } from "webdav";
import { generateRemoteUrl } from "@nextcloud/router";
import { getCurrentUser, getRequestToken } from "@nextcloud/auth";
import { router } from "../../router";
import "@nextcloud/dialogs/styles/toast.scss";
import HomeView from "../HomeView.vue";
import ImagesView from "../ImagesView.vue";

export default {
  name: "App",
  components: {
    NcAppContent,
    NcAppNavigation,
    NcModal,
    NcButton,
    NcTextField,
    NcHeaderMenu,
    NcEmptyContent,
  },
  data() {
    return {
      directories: [],
      files: [],
      folderName: "",
      fields: [
        { key: "basename", label: "Name" },
        { key: "lastmod", label: "Lastmod" },
        { key: "size", label: "Size" },
      ],
      pathToRedirect: "/apps/thirdapp",
      paths: [],
    };
  },
  computed: {},
  async mounted() {
    const path = window.location.pathname.replace("/apps/thirdapp/", "");
    await this.getFolders(path);
    console.log(path);
  },
  watch: {
    $route(to, from) {
      console.log(to, from);
      if (from.path.length > to.path.length) {
        const path = from.path.replace(to.path, "");
        const pathItems = [];
        this.paths.map((item) => {
          if (!path.includes(item.key)) {
            pathItems.push(item);
          }
        });
        this.paths = [...pathItems];
        this.pathToRedirect = this.pathToRedirect.replace(path, "");
        let parseToSet = this.pathToRedirect;
        if (this.pathToRedirect.endsWith("/")) {
          parseToSet = this.pathToRedirect.slice(0, -1);
        }
        router.push(parseToSet);
      } else {
        const path = to.path.replace(from.path, "");
        if (!path.includes("/")) {
          this.paths.push({
            text: path,
            key: path,
          });
        } else {
          this.paths.push({
            text: path.replace("/", ""),
            key: path.replace("/", ""),
          });
        }
      }
      const path = window.location.pathname.replace("/apps/thirdapp/", "");
      this.getFolders(path);
    },
  },
  methods: {
    checkFolderIncludeImg(directories) {
      const isExist = directories.find((item) => item.basename.includes("jpg"));
      return isExist ? true : false;
    },
    async showImges() {
      router.push("/images");
    },
    async onRowClicked(item, index, event) {
      this.pathToRedirect += "/" + item.basename;
      router.push(this.pathToRedirect);
    },

    async getFolders(pathToRedirect) {
      this.directories = [];
      const client = createClient(generateRemoteUrl("dav"), {
        headers: { Requesttoken: getRequestToken() },
      });
      console.log(getCurrentUser().uid);
      const response = await client.getDirectoryContents(
        `/files/${getCurrentUser()?.uid}/${pathToRedirect}`,
        {
          data: `<?xml version="1.0"?>
                      <d:propfind  xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns" xmlns:nc="http://nextcloud.org/ns">
                        <d:prop>
                          <d:getcontenttype />
                          <d:getlastmodified />
                          <oc:owner-id />
                          <oc:size />
                          <d:resourcetype />
                          <nc:contained-file-count />
                        </d:prop>
                      </d:propfind>`,
        }
      );
      debugger;
      if (pathToRedirect) {
        response.forEach((item) => {
          this.directories.push(item);
        });
      } else {
        response.forEach((item) => {
          if (item.type === "directory") {
            this.directories.push(item);
          }
        });
      }

      console.log(this.directories);
      console.log(response);
      return response;
    },

    async getFiles(dirName) {
      const client = createClient(generateRemoteUrl("dav"), {
        headers: { Requesttoken: getRequestToken() },
      });
      console.log(getCurrentUser().uid);
      const response = await client.getDirectoryContents(
        `/files/${getCurrentUser()?.uid}/${dirName}`,
        {
          data: `<?xml version="1.0"?>
                      <d:propfind  xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns" xmlns:nc="http://nextcloud.org/ns">
                        <d:prop>
                          <d:getcontenttype />
                          <d:getlastmodified />
                          <oc:owner-id />
                          <oc:size />
                          <d:resourcetype />
                          <nc:contained-file-count />
                        </d:prop>
                      </d:propfind>`,
        }
      );
      this.files = [...response];
    },
    async createNewFolder(newFolderName) {
      const client = createClient(generateRemoteUrl("dav"), {
        headers: { Requesttoken: getRequestToken() },
      });
      console.log(getCurrentUser().uid);
      const response = await client.createDirectory(
        `/files/${getCurrentUser()?.uid}/${newFolderName}`
      );
      console.log(response);
    },
  },
};
</script>
<style scoped>
#app-content > div {
  width: 100%;
  height: 100%;
  padding: 20px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}
.add-new {
  margin-top: 30px;
}
.btn-directory {
  width: 100%;
  display: flex;
  justify-content: space-between;
  border-top-style: solid;
  border-right-style: none;
  border-bottom-style: solid;
  border-left-style: none;
  color: #000;
}
.btn-folder-name {
  display: flex;
  justify-content: center;
  align-items: center;

  text-align: left;
  color: #000;
}
.btn-actions {
  text-align: right;
}
input[type="text"] {
  width: 100%;
}
.file-container {
  margin-top: 40px;
}
textarea {
  flex-grow: 1;
  width: 100%;
}
.item {
  display: flex;
  border: 1px solid #c2dedc;
  margin-bottom: 20px;
  border-radius: 14px;
  text-align: center;
  color: red;
  justify-content: space-between;
}
.item span {
  margin-top: 10px;
}
.content_container {
  /* display: flex;
          align-items: center;
          justify-content: center;
          flex-direction: column; */
  margin-top: 50px;
}
.download {
  text-align: end;
}
.label {
  background-color: indigo;
  color: white;
  padding: 0.5rem;
  font-family: sans-serif;
  border-radius: 0.3rem;
  cursor: pointer;
  margin-top: 1rem;
}
.upload {
  margin-top: 20px;
  border: none;
}
</style>
