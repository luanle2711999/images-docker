<template>
  <div class="app-snextcloud">
    <NcAppContent>
      <div class="tree-container">
        <!-- <button @click="addNode">Add Node</button> -->
        <!-- @click="
            (params) => {
              $emit('queryImgesAfterFetch', params?.id);
            }
          " -->
        <vue-tree-list
          @click="
            (params) => {
              $emit('queryImgesAfterFetch', params?.dateTaken, params?.type);
            }
          "
          @change-name="onChangeName"
          @delete-node="onDel"
          @add-node="onAddNode"
          :model="dataSoureResult"
          default-tree-node-name="new node"
          default-leaf-node-name="new leaf"
          v-bind:default-expanded="false"
        >
          <template v-slot:leafNameDisplay="slotProps">
            <div>
              {{ slotProps?.model?.name || "Unknown Date" }} ({{
                slotProps?.model?.amount
              }}
              {{ slotProps?.model?.amount > 1 ? "items" : "item" }})
            </div>
          </template>
          <template v-slot:addTreeNodeIcon="slotProps">
            <span class="icon">{{ "" }}</span>
          </template>
          <template v-slot:addLeafNodeIcon="slotProps">
            <span class="icon">{{ "" }}</span>
          </template>
          <template v-slot:editNodeIcon="slotProps">
            <span class="icon">{{ "" }}</span>
          </template>
          <template v-slot:delNodeIcon="slotProps">
            <span class="icon">{{ "" }}</span>
          </template>
          <template v-slot:leafNodeIcon="slotProps">
            <span class="icon">{{ "" }}</span>
          </template>
          <template v-slot:treeNodeIcon="slotProps">
            <span class="icon"> {{ "📂" }}</span>
          </template>
        </vue-tree-list>
      </div>
    </NcAppContent>
  </div>
</template>

<script>
import { VueTreeList, Tree, TreeNode } from "vue-tree-list";
import NcAppContent from "@nextcloud/vue/dist/Components/NcAppContent";
export default {
  components: {
    VueTreeList,
    NcAppContent,
  },
  data() {
    return {
      newTree: {},
      data: new Tree(this.dataSource),
    };
  },
  props: ["dataSource", "test"],
  computed: {
    dataSoureResult: function () {
      return new Tree(this.dataSource);
    },
  },
  methods: {
    onDel(node) {
      console.log(node);
      node.remove();
    },

    onChangeName(params) {
      console.log(params);
    },

    onAddNode(params) {
      console.log(params);
    },

    onClick(params) {
      console.log(params);
    },

    addNode() {
      var node = new TreeNode({ name: "new node", isLeaf: false });
      if (!this.data.children) this.data.children = [];
      this.data.addChildren(node);
    },
  },
};
</script>

<!-- <style lang="less" rel="stylesheet/less">
.vtl {
  .vtl-drag-disabled {
    background-color: #d0cfcf;
    &:hover {
      background-color: #d0cfcf;
    }
  }
  .vtl-disabled {
    background-color: #d0cfcf;
  }
}
</style> -->

<style>
/* .icon {
  &:hover {
    cursor: pointer;
  }
} */
.icon :hover {
  cursor: pointer;
}

.muted {
  color: gray;
  font-size: 80%;
}
/* .vtl {
  .vtl-drag-disabled {
    background-color: #d0cfcf;
    &:hover {
      background-color: #d0cfcf;
    }
  }
  .vtl-disabled {
    background-color: #d0cfcf;
  }
} */
/* .vtl .vtl-drag-disabled {
  background-color: #d0cfcf;
} */
.vtl .vtl-drag-disabled :hover :focus {
  background-color: #d0cfcf;
}

.vtl .vtl-disabled :focus {
  background-color: #d0cfcf;
}
.vtl-operation:focus {
  background-color: red;
}
.vtl-node-main:focus {
  background-color: red;
}
.tree-container {
  color: var(--neb-bg-0);
}
.tree-folder:focus {
  background-color: blue;
}
</style>
