<script setup lang="ts">
import type { NavigationMenuContentEmits, NavigationMenuContentProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import {
  NavigationMenuContent,

  useForwardPropsEmits,
} from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<NavigationMenuContentProps & { class?: HTMLAttributes["class"] }>()

const emits = defineEmits<NavigationMenuContentEmits>()

const delegatedProps = reactiveOmit(props, "class")

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <NavigationMenuContent
    v-bind="forwarded"
    :class="cn(
      'tw-left-0 tw-top-0 tw-w-full data-[motion^=from-]:tw-animate-in data-[motion^=to-]:tw-animate-out data-[motion^=from-]:tw-fade-in data-[motion^=to-]:tw-fade-out data-[motion=from-end]:tw-slide-in-from-right-52 data-[motion=from-start]:tw-slide-in-from-left-52 data-[motion=to-end]:tw-slide-out-to-right-52 data-[motion=to-start]:tw-slide-out-to-left-52 md:tw-absolute md:tw-w-auto',
      props.class,
    )"
  >
    <slot />
  </NavigationMenuContent>
</template>
