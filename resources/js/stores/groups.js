import { defineStore } from "pinia";
import { ref } from "vue";

export const useGroupsStore = defineStore("groups", () => {
  const groups = ref([]);

  const addGroup = (group) => {
    groups.value.push(group);
  };

  return { groups, addGroup };
});
