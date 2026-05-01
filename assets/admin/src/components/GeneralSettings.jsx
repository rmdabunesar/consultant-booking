import {
  ToggleControl,
  SelectControl,
  TextControl,
} from "@wordpress/components";
import PageSelector from "./fields/PageSelector";

const GeneralSettings = ({ settings, update }) => {
  return (
    <div style={{ maxWidth: "300px" }}>
      <ToggleControl
        label="Enable Booking System"
        checked={settings.booking_enabled || false}
        onChange={(value) => update("booking_enabled", value)}
      />

      <SelectControl
        label="Currency"
        value={settings.currency_code}
        options={[
          { label: "US Dollar (USD)", value: "USD" },
          { label: "Euro (EUR)", value: "EUR" },
          { label: "Indian Rupee (INR)", value: "INR" },
        ]}
        onChange={(value) => update("currency_code", value)}
      />

      <SelectControl
        label="Currency Symbol Position"
        value={settings.currency_position}
        options={[
          { label: "Left ($99)", value: "left" },
          { label: "Right (99$)", value: "right" },
        ]}
        onChange={(value) => update("currency_position", value)}
      />

      <TextControl
        label="Doctors Per Page"
        type="number"
        value={settings.consultants_per_page}
        onChange={(val) => update("consultants_per_page", parseInt(val, 10))}
      />

      <PageSelector
			  label="Booking Page"
        value={settings.booking_page_id}
        onChange={(val) => update("booking_page_id", parseInt(val, 10))}
      />

      <PageSelector
			  label="Success Page"
        value={settings.success_page_id}
        onChange={(val) => update("success_page_id", parseInt(val, 10))}
      />
      
      <PageSelector
			  label="Cancel Page"
        value={settings.cancel_page_id}
        onChange={(val) => update("cancel_page_id", parseInt(val, 10))}
      />
    </div>
  );
};

export default GeneralSettings;
