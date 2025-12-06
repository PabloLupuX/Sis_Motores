import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Button } from "./Button.vue"

export const buttonVariants = cva(
  'tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-whitespace-nowrap tw-rounded-md tw-text-sm tw-font-medium tw-transition-colors focus-visible:tw-outline-none focus-visible:tw-ring-1 focus-visible:tw-ring-slate-950 disabled:tw-pointer-events-none disabled:tw-opacity-50 [&_svg]:tw-pointer-events-none [&_svg]:tw-size-4 [&_svg]:tw-shrink-0 dark:focus-visible:tw-ring-slate-300',
  {
    variants: {
      variant: {
        default: 'tw-bg-slate-900 tw-text-slate-50 tw-shadow hover:tw-bg-slate-900/90 dark:tw-bg-slate-50 dark:tw-text-slate-900 dark:hover:tw-bg-slate-50/90',
        destructive:
          'tw-bg-red-500 tw-text-slate-50 tw-shadow-sm hover:tw-bg-red-500/90 dark:tw-bg-red-900 dark:tw-text-slate-50 dark:hover:tw-bg-red-900/90',
        outline:
          'tw-border tw-border-slate-200 tw-bg-white tw-shadow-sm hover:tw-bg-slate-100 hover:tw-text-slate-900 dark:tw-border-slate-800 dark:tw-bg-slate-950 dark:hover:tw-bg-slate-800 dark:hover:tw-text-slate-50',
        secondary:
          'tw-bg-slate-100 tw-text-slate-900 tw-shadow-sm hover:tw-bg-slate-100/80 dark:tw-bg-slate-800 dark:tw-text-slate-50 dark:hover:tw-bg-slate-800/80',
        ghost: 'hover:tw-bg-slate-100 hover:tw-text-slate-900 dark:hover:tw-bg-slate-800 dark:hover:tw-text-slate-50',
        link: 'tw-text-slate-900 tw-underline-offset-4 hover:tw-underline dark:tw-text-slate-50',
      },
      size: {
        "default": 'tw-h-9 tw-px-4 tw-py-2',
        "xs": 'tw-h-7 tw-rounded tw-px-2',
        "sm": 'tw-h-8 tw-rounded-md tw-px-3 tw-text-xs',
        "lg": 'tw-h-10 tw-rounded-md tw-px-8',
        "icon": 'tw-h-9 tw-w-9',
        "icon-sm": 'tw-size-8',
        "icon-lg": 'tw-size-10',
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  },
)

export type ButtonVariants = VariantProps<typeof buttonVariants>
