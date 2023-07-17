// SPDX-FileCopyrightText: Lê Công Luận <lecongluan99@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later
module.exports = {
  extends: ["@nextcloud"],
  rules: {
    indent: [
      "error",
      2,
      {
        SwitchCase: 1,
        ignoredNodes: ["ConditionalExpression"],
      },
    ],
  },
  "vue/singleline-html-element-content-newline": "off",
  "vue/multiline-html-element-content-newline": "off",
};
