<template>
  <div id="content" class="app-snextcloud">
    <AppNavigation>
      <!-- <AppNavigationNew
		  v-if="!loading"
		  :text="t('snextcloud', 'Files')"
		  :disabled="false"
		  button-id="new-snextcloud-button"
		  button-class="icon-add"
		  @click="openModal"
		/> -->
      <input type="file" id="actual-btn" class="upload" />
      <button @click="uploadFile">upload</button>
    </AppNavigation>
    <AppContent>
      <div class="content_container">
        <div
          v-for="note in files"
          :key="note.id"
          :title="note.basename ? note.basename : t('snextcloud', 'Files')"
          class="item"
        >
          <span>{{ note.basename }}</span>
          <div>
            <button
              class="download"
              @click="
                () => {
                  downloadSingleFile(note.basename);
                }
              "
            >
              download
            </button>
            <button
              class="delete"
              @click="
                () => {
                  deleteSingleFile(note.basename);
                }
              "
            >
              delete
            </button>
          </div>
        </div>
      </div>
    </AppContent>
    <NcModal v-if="visible" @close="closeModal" name="Name inside modal">
      <div class="modal__content">
        <h2>Please enter your name</h2>
        <NcTextField label="First Name" :value.sync="firstName" />
        <NcTextField label="Last Name" :value.sync="lastName" />
        <NcButton
          :disabled="!this.firstName || !this.lastName"
          @click="closeModal"
          type="primary"
        >
          Submit
        </NcButton>
      </div>
    </NcModal>
  </div>
</template>

<script>
import ActionButton from "@nextcloud/vue/dist/Components/ActionButton";
import AppContent from "@nextcloud/vue/dist/Components/AppContent";
import AppNavigation from "@nextcloud/vue/dist/Components/AppNavigation";
import AppNavigationItem from "@nextcloud/vue/dist/Components/AppNavigationItem";
import AppNavigationNew from "@nextcloud/vue/dist/Components/AppNavigationNew";
import { NcModal, NcButton, NcTextField } from "@nextcloud/vue";
import { createClient, AuthType } from "webdav";
import { generateRemoteUrl } from "@nextcloud/router";
import { getCurrentUser, getRequestToken } from "@nextcloud/auth";

import "@nextcloud/dialogs/styles/toast.scss";

export default {
  name: "App",
  components: {
    ActionButton,
    AppContent,
    AppNavigation,
    AppNavigationItem,
    AppNavigationNew,
    NcModal,
    NcButton,
    NcTextField,
  },
  data() {
    return {
      files: [],
      updating: false,
      loading: true,
      visible: false,
    };
  },
  computed: {},
  async mounted() {
    try {
      this.getFiles();
    } catch (e) {
      console.error(e);
    }
    this.loading = false;
  },
  methods: {
    async getFiles() {
      const client = createClient(generateRemoteUrl("dav"), {
        headers: { Requesttoken: getRequestToken() },
      });
      console.log(getCurrentUser().uid);
      const response = await client.getDirectoryContents(
        `/files/${getCurrentUser()?.uid}/Documents`,
        {
          data: `<?xml version="1.0"?>
					<d:propfind  xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns" xmlns:nc="http://nextcloud.org/ns">
					  <d:prop>
						<d:getcontenttype />
						<d:getlastmodified />
						<oc:owner-id />
						<oc:size />
					  </d:prop>
					</d:propfind>`,
        }
      );
      this.files = [...response];
      return response;
    },

    async readSingleFile(fileName) {
      const reader = new FileReader();
      reader.addEventListener("load", (event) => {
        img.src = event.target.result;
      });
      reader.readAsDataURL(file);
    },
    async deleteSingleFile(fileName) {
      const client = createClient(
        generateRemoteUrl(`dav/files/${getCurrentUser()?.uid}/Documents`),
        {
          headers: { Requesttoken: getRequestToken() },
        }
      );
      await client.deleteFile(fileName);
    },
    async downloadSingleFile(fileName) {
      const url = `${generateRemoteUrl("dav")}/files/${
        getCurrentUser()?.uid
      }/Documents/${fileName}`;
      const client = createClient(
        generateRemoteUrl(`dav/files/${getCurrentUser()?.uid}/Documents`),
        {
          headers: { Requesttoken: getRequestToken() },
        }
      );
      const response = await client.getFileContents(fileName, {
        details: true,
        format: "text",
      });
      const link = document.createElement("a");
      link.href = url;
      link.setAttribute("download", fileName); //or any other extension
      document.body.appendChild(link);
      link.click();
      return response;
    },

    async uploadFile() {
      const fileInput = document.getElementById("actual-btn");
      const reader = new FileReader();
      reader.onload = async (e) => {
        console.log(e.target.result);
        const client = createClient(
          generateRemoteUrl(`dav/files/${getCurrentUser()?.uid}/Documents`),
          {
            headers: { Requesttoken: getRequestToken() },
          }
        );
        await client.putFileContents(fileInput.files[0].name, e.target.result);
      };
      reader.readAsText(fileInput.files[0]);
    },
    openModal() {
      this.visible = true;
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

input[type="text"] {
  width: 100%;
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
