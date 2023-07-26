<template>
  <div id="content" class="app-snextcloud" v-if="!check">
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
        <button @click="goBack" :disabled="!this.currentPath">back</button>
        <button
          @click="goForward"
          :disabled="this.forwardPath === this.currentPath"
        >
          forward
        </button>
        <b-breadcrumb :items="paths"></b-breadcrumb>
        <b-table
          :fields="fields"
          :items="directories"
          class="table table-hover"
          @row-clicked="onRowClicked"
        >
          <template #cell(basename)="data" class="text-info">
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
          <template #cell(size)="data">
            <span class="text-info"
              >{{ Math.round(data?.item?.props?.size / 1000) }} KB</span
            >
          </template>
        </b-table>
        <button @click="showImges()" v-if="checkFolderIncludeImg(directories)">
          Live show
        </button>
      </div>
    </NcAppContent>
  </div>
  <ImagesView
    :file-id="this.fileId"
    :folder-name="this.folderName"
    v-on:goBackward="goBackward"
    v-else
  />
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
    ImagesView,
  },
  data() {
    return {
      check: false,
      fileId: 0,
      folderName: "",
      directories: [],
      files: [],
      folderName: "",
      fields: [
        { key: "basename", label: "Name" },
        { key: "lastmod", label: "Lastmod" },
        { key: "size", label: "Size" },
      ],
      paths: [],
      currentPath: "",
      forwardPath: "",
    };
  },
  computed: {},
  async mounted() {
    await this.getFolders("");
  },
  watch: {
    gapForward: (newGapForward, oldGapForward) => {
      this.trackGap = newGapForward[newGapForward.length - 1];
    },
  },
  methods: {
    checkFolderIncludeImg(directories) {
      const isExist = directories.find((item) => item.basename.includes("jpg"));
      return isExist ? true : false;
    },
    async showImges() {
      this.check = true;
    },
    async onRowClicked(item, index, event) {
      this.fileId = item.props.fileid;
      this.folderName = item.basename;
      this.currentPath += "/" + item.basename;
      this.forwardPath = this.currentPath;
      this.paths.push({
        text: item.basename,
        href: "",
      });
      this.getFolders(this.currentPath);
    },

    async goBack() {
      const pathArr = this.forwardPath.split("/");

      if (pathArr && pathArr.length > 0) {
        const newPath = this.currentPath.split("/").slice(0, -1).join("/");
        const removedPath = this.currentPath.replace(`${newPath}/`, "");
        this.currentPath = newPath;
        let newPathToSetBreadcrumb = [];
        this.paths.forEach((item) => {
          if (item.text !== removedPath) {
            newPathToSetBreadcrumb.push(item);
          }
        });
        this.paths = [...newPathToSetBreadcrumb];
        await this.getFolders(this.currentPath);
      } else {
        await this.getFolders("");
      }
    },

    goBackward() {
      this.check = false;
    },
    async goForward() {
      if (this.forwardPath) {
        const gapTotal = this.forwardPath.replace(`${this.currentPath}/`, "");
        if (gapTotal.includes("/")) {
          const gapArr = gapTotal.split("/");
          const pathToForward = this.currentPath + "/" + gapArr[0];
          this.currentPath = pathToForward;
          this.paths.push({
            text: gapArr[0],
            href: "",
          });
          await this.getFolders(pathToForward);
        } else {
          const pathToForward = this.currentPath + "/" + gapTotal;
          this.paths.push({
            text: gapTotal,
            href: "",
          });
          this.currentPath = pathToForward;
          await this.getFolders(pathToForward);
        }
      }
    },
    async getFolders(pathToRedirect) {
      this.directories = [];
      const client = createClient(generateRemoteUrl("dav"), {
        headers: { Requesttoken: getRequestToken() },
      });
      console.log(getCurrentUser().uid);
      const response = await client.getDirectoryContents(
        `/files/${getCurrentUser()?.uid}${pathToRedirect}`,
        {
          data: `<?xml version="1.0"?>
            <d:propfind  xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns" xmlns:nc="http://nextcloud.org/ns">
                <d:prop>
                <d:getcontenttype />
                <d:getlastmodified />
                <oc:owner-id />
                <oc:size />
                <oc:fileid />
                <d:resourcetype />
                <nc:contained-file-count />
                </d:prop>
            </d:propfind>`,
          details: true,
        }
      );
      console.log({ response });
      const abcdef = await client.getFileContents("");
      debugger;
      if (pathToRedirect) {
        response.data.forEach((item) => {
          this.directories.push(item);
        });
      } else {
        response.data.forEach((item) => {
          if (item.type === "directory") {
            this.directories.push(item);
          }
        });
      }

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
