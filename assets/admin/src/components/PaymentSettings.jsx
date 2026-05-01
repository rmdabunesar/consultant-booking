import { TextControl, ToggleControl } from "@wordpress/components";

const PaymentSettings = ({ settings, update }) => {
  return (
    <div style={{ maxWidth: "300px" }}>

      <TextControl
        label="Secret Key"
        value={settings.secret_key || ""}
        onChange={(value) => update("secret_key", value)}
      />

      <TextControl
        label="Publishable Key"
        value={settings.publishable_key || ""}
        onChange={(value) => update("publishable_key", value)}
      />
    </div>
  );
};

export default PaymentSettings;
